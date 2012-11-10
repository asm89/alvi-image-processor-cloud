class alvi::queuestats($alvi_dir = '/data') {
    
    file {"${alvi_dir}/app/console":
        ensure => file
    }
    
    file {'/etc/init/alvi-queuestats.conf':
        ensure  => file,
        owner   => root,
        group   => root,
        mode    => '0644',
        require => Service['zookeeper', 'rabbitmq-server'],
        content => template('alvi/alvi-queuestats.conf.erb'),
        notify  => Service["alvi-queuestats"];
    }

    service {
        "alvi-queuestats":
            hasstatus => true,
            hasrestart => true,
            ensure => running,
            enable => true,
            require => File["/etc/init/alvi-queuestats.conf"];
    }
}
