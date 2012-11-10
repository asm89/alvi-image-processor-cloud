class alvi::provisionercallback($alvi_dir = '/data') {

    file {'/etc/init/alvi-provisionercallback.conf':
        ensure  => file,
        owner   => root,
        group   => root,
        mode    => '0644',
        require => Service['zookeeper', 'rabbitmq-server'],
        content => template('alvi/alvi-provisionercallback.conf.erb'),
        notify  => Service["alvi-provisionercallback"];
    }

    service {
        "alvi-provisionercallback":
            hasstatus => true,
            hasrestart => true,
            ensure => running,
            enable => true,
            require => File["/etc/init/alvi-provisionercallback.conf"];
    }
}
