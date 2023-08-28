%define modname security

Summary: Issabel Security
Name: issabel-security
Version: 5.0.0
Release: 1
License: GPL
Group:   Applications/System
Source0: issabel-%{modname}-%{version}.tar.gz
BuildRoot: %{_tmppath}/%{name}-%{version}-root
BuildArch: noarch
Requires(pre): issabel-framework >= 5.0.0
Requires(pre): issabelPBX >= 2.12.0
Requires(pre): iptables
# On CentOS 7 only, iptables does *not* install any service files
Requires(pre): iptables-services
Requires: issabel-system
Requires: issabel-portknock
Requires: net-tools
Requires: fail2ban-server
Requires: fail2ban-sendmail
Requires: ipset-service
Requires: php-pecl-geoip
Requires: sqlite
Requires: certbot
Requires: python3-certbot-apache

# sec_weak_keys pulls extensions_batch/libs/paloSantoExtensionsBatch.class.php
# to perform asterisk reload
Requires: issabel-pbx >= 4.0.0-0

# commands: cut
Requires: coreutils

# /usr/share/issabel/privileged/anonymoussip recarga asterisk
# Requires: asterisk

Obsoletes: elastix-security
Obsoletes: issabel-geoip_key
Provides: issabel-geoip_key

%description
Issabel Security

%prep
%setup -n %{name}-%{version}

%install
rm -rf $RPM_BUILD_ROOT

# Files provided by all Issabel modules
mkdir -p    $RPM_BUILD_ROOT%{_localstatedir}/www/html/
mkdir -p    $RPM_BUILD_ROOT%{_datadir}/issabel/privileged
mkdir -p    $RPM_BUILD_ROOT/usr/src/portknock-client-sample
mv setup/usr/src/portknock-client-sample/* $RPM_BUILD_ROOT/usr/src/portknock-client-sample
rmdir setup/usr/src/portknock-client-sample
rmdir setup/usr/src

mv modules/ $RPM_BUILD_ROOT%{_localstatedir}/www/html/
mv setup/usr/share/issabel/privileged/*  $RPM_BUILD_ROOT%{_datadir}/issabel/privileged
rmdir setup/usr/share/issabel/privileged

chmod +x setup/updateDatabase
chmod +x setup/reloadIssabelconf

# Crontab for portknock authorization cleanup
mkdir -p $RPM_BUILD_ROOT%{_sysconfdir}/cron.d/
cp setup/etc/cron.d/issabel-portknock.cron $RPM_BUILD_ROOT%{_sysconfdir}/cron.d/
chmod 644 $RPM_BUILD_ROOT%{_sysconfdir}/cron.d/issabel-portknock.cron
mkdir -p $RPM_BUILD_ROOT%{_sysconfdir}/cron.daily/
cp setup/etc/cron.daily/renewssl $RPM_BUILD_ROOT%{_sysconfdir}/cron.daily/
cp setup/etc/cron.daily/purgeattacks $RPM_BUILD_ROOT%{_sysconfdir}/cron.daily/

mkdir -p $RPM_BUILD_ROOT%{_sysconfdir}/fail2ban/jail.d/
cp setup/etc/fail2ban/jail.d/issabel.conf $RPM_BUILD_ROOT%{_sysconfdir}/fail2ban/jail.d
chmod 644 $RPM_BUILD_ROOT%{_sysconfdir}/fail2ban/jail.d/issabel.conf

mkdir -p $RPM_BUILD_ROOT%{_sysconfdir}/fail2ban/filter.d/
cp setup/etc/fail2ban/filter.d/asterisk-ami.conf $RPM_BUILD_ROOT%{_sysconfdir}/fail2ban/filter.d
chmod 644 $RPM_BUILD_ROOT%{_sysconfdir}/fail2ban/filter.d/asterisk-ami.conf
cp setup/etc/fail2ban/filter.d/issabel-gui.conf $RPM_BUILD_ROOT%{_sysconfdir}/fail2ban/filter.d
chmod 644 $RPM_BUILD_ROOT%{_sysconfdir}/fail2ban/filter.d/issabel-gui.conf

mkdir -p $RPM_BUILD_ROOT%{_sysconfdir}/fail2ban/action.d/
cp setup/etc/fail2ban/action.d/iptables-multiport-issabel.conf $RPM_BUILD_ROOT%{_sysconfdir}/fail2ban/action.d
chmod 644 $RPM_BUILD_ROOT%{_sysconfdir}/fail2ban/action.d/iptables-multiport-issabel.conf

# Startup service for portknock
mkdir -p $RPM_BUILD_ROOT%{_sysconfdir}/rc.d/init.d/
cp setup/etc/rc.d/init.d/issabel-portknock $RPM_BUILD_ROOT%{_sysconfdir}/rc.d/init.d/
chmod 755 $RPM_BUILD_ROOT%{_sysconfdir}/rc.d/init.d/issabel-portknock

# Portknock-related utilities
mkdir -p $RPM_BUILD_ROOT%{_bindir}/
mv setup/usr/bin/issabel-portknock* $RPM_BUILD_ROOT%{_bindir}/
chmod 755 $RPM_BUILD_ROOT%{_bindir}/issabel-portknock*
rmdir setup/usr/bin

rmdir setup/usr/share/issabel setup/usr/share setup/usr

# The following folder should contain all the data that is required by the installer,
# that cannot be handled by RPM.
mkdir -p    $RPM_BUILD_ROOT%{_datadir}/issabel/module_installer/%{name}-%{version}-%{release}/
mv setup/   $RPM_BUILD_ROOT%{_datadir}/issabel/module_installer/%{name}-%{version}-%{release}/
mv menu.xml $RPM_BUILD_ROOT%{_datadir}/issabel/module_installer/%{name}-%{version}-%{release}/

%pre
mkdir -p %{_datadir}/issabel/module_installer/%{name}-%{version}-%{release}/
touch %{_datadir}/issabel/module_installer/%{name}-%{version}-%{release}/preversion_%{modname}.info
if [ $1 -eq 2 ]; then
    rpm -q --queryformat='%{VERSION}-%{RELEASE}' %{name} > %{_datadir}/issabel/module_installer/%{name}-%{version}-%{release}/preversion_%{modname}.info
fi

%post
pathModule="%{_datadir}/issabel/module_installer/%{name}-%{version}-%{release}"

# Run installer script to fix up ACLs and add module to Issabel menus.
issabel-menumerge $pathModule/menu.xml
pathSQLiteDB="%{_localstatedir}/www/db"
mkdir -p $pathSQLiteDB
preversion=`cat $pathModule/preversion_%{modname}.info`
rm $pathModule/preversion_%{modname}.info

if [ $1 -eq 1 ]; then #install
    # The installer database
    issabel-dbprocess "install" "$pathModule/setup/db"
    $pathModule/setup/reloadIssabelconf install
elif [ $1 -eq 2 ]; then #update
    # The update database
    $pathModule/setup/checkFields "$preversion" "$pathModule"
    issabel-dbprocess "update"  "$pathModule/setup/db" "$preversion"
    $pathModule/setup/updateDatabase "$preversion"
    $pathModule/setup/reloadIssabelconf upgrade
fi


# Create line in jail.local
if [ -f /etc/fail2ban/jail.local ]; then
grep "START issabel" /etc/fail2ban/jail.local &>/dev/null
if [ $? -eq 1 ]; then
cat >> /etc/fail2ban/jail.local <<'ISSABELJAILLOCAL'
#START issabel
[DEFAULT]
chain=F2B_INPUT
#END issabel
ISSABELJAILLOCAL
fi
else
cat >> /etc/fail2ban/jail.local <<'ISSABELJAILLOCAL'
#START issabel
[DEFAULT]
chain=F2B_INPUT
#END issabel
ISSABELJAILLOCAL
fi

# The installer script expects to be in /tmp/new_module
mkdir -p /tmp/new_module/%{modname}
cp -r $pathModule/* /tmp/new_module/%{modname}/
chown -R asterisk.asterisk /tmp/new_module/%{modname}

php /tmp/new_module/%{modname}/setup/installer.php
rm -rf /tmp/new_module

%{_datadir}/issabel/privileged/anonymoussip --conddisable

# Install issabel-portknock as a service
chkconfig --add issabel-portknock
chkconfig --level 2345 issabel-portknock on

chgrp asterisk /etc/fail2ban/jail.d
chmod g+w /etc/fail2ban/jail.d
chown asterisk.asterisk /etc/fail2ban/jail.d/issabel.conf
systemctl enable fail2ban
systemctl enable ipset

mkdir /etc/sysconfig/ipset.d
echo "create issabel_whitelist hash:ip family inet hashsize 1024 maxelem 65536" >/etc/sysconfig/ipset.d/issabel_whitelist.set
touch /etc/sysconfig/ipset.d/.saved

/usr/share/issabel/privileged/fwconfig --save_wl

if [ $1 -eq 2 ]; then #upgrade
    %{_datadir}/issabel/privileged/fwconfig --isactive >/dev/null
    if [ $? -eq 0 ]; then
        echo "Restarting Firewall"
        /usr/share/issabel/privileged/fwconfig --flush
        /usr/share/issabel/privileged/fwconfig --load
    fi
fi

/usr/bin/sqlite3 /var/www/db/attacks.db "CREATE TABLE IF NOT EXISTS attacks (source text not null, datetime datetime, done int default 0, ip text)"
chown asterisk.asterisk /var/www/db/attacks.db

/usr/share/issabel/privileged/ssl_certbot writeasteriskcert
chown asterisk.asterisk /etc/asterisk/keys -R

%postun
sed -i '/#START issabel/,/#END issabel/d' /etc/fail2ban/jail.local

%clean
rm -rf $RPM_BUILD_ROOT

%preun
pathModule="%{_datadir}/issabel/module_installer/%{name}-%{version}-%{release}"

if [ $1 -eq 0 ] ; then # Validation for desinstall this rpm
  echo "Delete Security menus"
  issabel-menuremove "%{modname}"

  echo "Dump and delete %{name} databases"
  issabel-dbprocess "delete" "$pathModule/setup/db"
fi

%files
%defattr(-, root, root)
%{_localstatedir}/www/html/*
%{_datadir}/issabel/module_installer/*
%defattr(644, root, root)
%{_sysconfdir}/cron.d/issabel-portknock.cron
%{_sysconfdir}/fail2ban/filter.d/asterisk-ami.conf
%{_sysconfdir}/fail2ban/filter.d/issabel-gui.conf
%{_sysconfdir}/fail2ban/action.d/iptables-multiport-issabel.conf
/usr/src/portknock-client-sample/portknock-client.go
%defattr(0755, root, root)
%{_datadir}/issabel/privileged/*
%{_sysconfdir}/rc.d/init.d/issabel-portknock
%{_sysconfdir}/cron.daily/renewssl
%{_sysconfdir}/cron.daily/purgeattacks
%{_bindir}/issabel-portknock-cleanup
%{_bindir}/issabel-portknock-validate

%config
%{_sysconfdir}/fail2ban/jail.d/issabel.conf

%changelog
