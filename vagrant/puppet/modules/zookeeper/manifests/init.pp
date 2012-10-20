class zookeeper($tick_time = 2000, $data_dir = '/var/lib/zookeeper', $client_port = 2181, $init_limit = 10, $sync_limit = 5) {

    package { 'zookeeperd':
        ensure => present;
    }

    file {'/etc/zookeeper/conf/zoo.cfg':
        ensure  => file,
        owner   => root,
        group   => root,
        mode    => '0644',
        require => Package['zookeeperd'],
        content => template('zookeeper/zoo.cfg.erb');
    }

    service {
        "zookeeper":
            hasstatus => true,
            hasrestart => true,
            ensure => running,
            enable => true,
            require => File["/etc/zookeeper/conf/zoo.cfg"];
    }
}
