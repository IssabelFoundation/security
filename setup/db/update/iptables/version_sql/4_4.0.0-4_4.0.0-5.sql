INSERT INTO port(name,protocol,details,comment) VALUES ('WEBRTC', 'TCP', '8088:8089', '');

UPDATE filter SET rule_order = rule_order + 1 WHERE rule_order >= (
    SELECT rule_order FROM filter WHERE dport = (SELECT id FROM port WHERE name = 'SIP')
);
INSERT INTO filter (traffic,eth_in,eth_out,ip_source,ip_destiny,protocol,sport,dport,icmp_type,number_ip,target,rule_order,activated)
VALUES ('INPUT','ANY','','0.0.0.0/0','0.0.0.0/0','TCP','ANY',
    (SELECT id FROM port WHERE name = 'WEBRTC'),
    '','','ACCEPT',
    (SELECT rule_order FROM filter WHERE dport = (SELECT id FROM port WHERE name = 'SIP')) - 1,
    1);


INSERT INTO port(name,protocol,details,comment) VALUES ('XMPP/WSS', 'TCP', '5281', '');
UPDATE filter SET rule_order = rule_order + 1 WHERE rule_order >= (
    SELECT rule_order FROM filter WHERE dport = (SELECT id FROM port WHERE name = 'SIP')
);
INSERT INTO filter (traffic,eth_in,eth_out,ip_source,ip_destiny,protocol,sport,dport,icmp_type,number_ip,target,rule_order,activated)
VALUES ('INPUT','ANY','','0.0.0.0/0','0.0.0.0/0','TCP','ANY',
    (SELECT id FROM port WHERE name = 'XMPP/WSS'),
    '','','ACCEPT',
    (SELECT rule_order FROM filter WHERE dport = (SELECT id FROM port WHERE name = 'SIP')) - 1,
    1);

UPDATE tmp_execute SET exec_in_sys = 0;
