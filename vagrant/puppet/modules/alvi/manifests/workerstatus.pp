class alvi::workerstatus($alvi_dir = '/data') {
    
    file {'/etc/init/alvi-workerstatus.conf':
        ensure  => file,
        owner   => root,
        group   => root,
        mode    => '0644',
        require => Service['zookeeper', 'rabbitmq-server'],
        content => template('alvi/alvi-workerstatus.conf.erb'),
        notify  => Service["alvi-workerstatus"];
    }

    service {
        "alvi-workerstatus":
            hasstatus => true,
            hasrestart => true,
            ensure => running,
            enable => true,
            require => File["/etc/init/alvi-workerstatus.conf"];
    }
}
