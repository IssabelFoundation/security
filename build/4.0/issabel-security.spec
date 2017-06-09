%define modname security

Summary: Issabel Security
Name:    issabel-%{modname}
Version: 4.0.0
Release: 1
License: GPL
Group:   Applications/System
Source0: %{modname}_%{version}-%{release}.tgz
BuildRoot: %{_tmppath}/%{name}-%{version}-root
BuildArch: noarch
Requires(pre): issabel-framework >= 2.3.0-5
Requires(pre): issabelPBX >= 2.8.1-2
Requires(pre): iptables
# On CentOS 7 only, iptables does *not* install any service files
Requires(pre): iptables-services
Requires: issabel-system
Requires: php-mcrypt
Requires: issabel-portknock
Requires: net-tools
Requires: fail2ban-server
Requires: fail2ban-sendmail

# sec_weak_keys pulls extensions_batch/libs/paloSantoExtensionsBatch.class.php
# to perform asterisk reload
Requires: issabel-pbx >= 4.0.0-0

# commands: cut
Requires: coreutils

# /usr/share/elastix/privileged/anonymoussip recarga asterisk
Requires: asterisk

Obsoletes: elastix-security

%description
Issabel Security

%prep
%setup -n %{name}_%{version}-%{release}

%install
rm -rf $RPM_BUILD_ROOT

# Files provided by all Elastix modules
mkdir -p    $RPM_BUILD_ROOT%{_localstatedir}/www/html/
mkdir -p    $RPM_BUILD_ROOT%{_datadir}/elastix/privileged
mv modules/ $RPM_BUILD_ROOT%{_localstatedir}/www/html/
mv setup/usr/share/elastix/privileged/*  $RPM_BUILD_ROOT%{_datadir}/elastix/privileged
rmdir setup/usr/share/elastix/privileged

chmod +x setup/updateDatabase

# Crontab for portknock authorization cleanup
mkdir -p $RPM_BUILD_ROOT%{_sysconfdir}/cron.d/
cp setup/etc/cron.d/elastix-portknock.cron $RPM_BUILD_ROOT%{_sysconfdir}/cron.d/
chmod 644 $RPM_BUILD_ROOT%{_sysconfdir}/cron.d/elastix-portknock.cron

mkdir -p $RPM_BUILD_ROOT%{_sysconfdir}/fail2ban/jail.d/
cp setup/etc/fail2ban/jail.d/issabel.conf $RPM_BUILD_ROOT%{_sysconfdir}/fail2ban/jail.d
chmod 644 $RPM_BUILD_ROOT%{_sysconfdir}/fail2ban/jail.d/issabel.conf

# Startup service for portknock
mkdir -p $RPM_BUILD_ROOT%{_sysconfdir}/rc.d/init.d/
cp setup/etc/rc.d/init.d/elastix-portknock $RPM_BUILD_ROOT%{_sysconfdir}/rc.d/init.d/
chmod 755 $RPM_BUILD_ROOT%{_sysconfdir}/rc.d/init.d/elastix-portknock

# Portknock-related utilities
mkdir -p $RPM_BUILD_ROOT%{_bindir}/
mv setup/usr/bin/elastix-portknock* $RPM_BUILD_ROOT%{_bindir}/
chmod 755 $RPM_BUILD_ROOT%{_bindir}/elastix-portknock*
rmdir setup/usr/bin

rmdir setup/usr/share/elastix setup/usr/share setup/usr

# The following folder should contain all the data that is required by the installer,
# that cannot be handled by RPM.
mkdir -p    $RPM_BUILD_ROOT%{_datadir}/elastix/module_installer/%{name}-%{version}-%{release}/
mv setup/   $RPM_BUILD_ROOT%{_datadir}/elastix/module_installer/%{name}-%{version}-%{release}/
mv menu.xml $RPM_BUILD_ROOT%{_datadir}/elastix/module_installer/%{name}-%{version}-%{release}/

%pre
mkdir -p %{_datadir}/elastix/module_installer/%{name}-%{version}-%{release}/
touch %{_datadir}/elastix/module_installer/%{name}-%{version}-%{release}/preversion_%{modname}.info
if [ $1 -eq 2 ]; then
    rpm -q --queryformat='%{VERSION}-%{RELEASE}' %{name} > %{_datadir}/elastix/module_installer/%{name}-%{version}-%{release}/preversion_%{modname}.info
fi

%post
pathModule="%{_datadir}/elastix/module_installer/%{name}-%{version}-%{release}"

# Run installer script to fix up ACLs and add module to Elastix menus.
elastix-menumerge $pathModule/menu.xml
pathSQLiteDB="%{_localstatedir}/www/db"
mkdir -p $pathSQLiteDB
preversion=`cat $pathModule/preversion_%{modname}.info`
rm $pathModule/preversion_%{modname}.info

if [ $1 -eq 1 ]; then #install
  # The installer database
    elastix-dbprocess "install" "$pathModule/setup/db"
elif [ $1 -eq 2 ]; then #update
   # The update database
      $pathModule/setup/checkFields "$preversion" "$pathModule"
      elastix-dbprocess "update"  "$pathModule/setup/db" "$preversion"
      $pathModule/setup/updateDatabase "$preversion"
fi

# The installer script expects to be in /tmp/new_module
mkdir -p /tmp/new_module/%{modname}
cp -r $pathModule/* /tmp/new_module/%{modname}/
chown -R asterisk.asterisk /tmp/new_module/%{modname}

php /tmp/new_module/%{modname}/setup/installer.php
rm -rf /tmp/new_module

%{_datadir}/elastix/privileged/anonymoussip --conddisable

# Install elastix-portknock as a service
chkconfig --add elastix-portknock
chkconfig --level 2345 elastix-portknock on

chgrp asterisk /etc/fail2ban/jail.d
chmod g+w /etc/fail2ban/jail.d
chown asterisk.asterisk /etc/fail2ban/jail.d/issabel.conf
systemctl enable fail2ban


%clean
rm -rf $RPM_BUILD_ROOT

%preun
pathModule="%{_datadir}/elastix/module_installer/%{name}-%{version}-%{release}"

if [ $1 -eq 0 ] ; then # Validation for desinstall this rpm
  echo "Delete Security menus"
  elastix-menuremove "%{modname}"

  echo "Dump and delete %{name} databases"
  elastix-dbprocess "delete" "$pathModule/setup/db"
fi

%files
%defattr(-, root, root)
%{_localstatedir}/www/html/*
%{_datadir}/elastix/module_installer/*
%defattr(644, root, root)
%{_sysconfdir}/cron.d/elastix-portknock.cron
%defattr(0755, root, root)
%{_datadir}/elastix/privileged/*
%{_sysconfdir}/rc.d/init.d/elastix-portknock
%{_sysconfdir}/fail2ban/jail.d/issabel.conf
%{_bindir}/elastix-portknock-cleanup
%{_bindir}/elastix-portknock-validate

%changelog
