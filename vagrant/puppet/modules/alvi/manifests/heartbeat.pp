class alvi::heartbeat($alvi_dir = '/data') {

    file {'/etc/init/alvi-heartbeat.conf':
        ensure  => file,
        owner   => root,
        group   => root,
        mode    => '0644',
        require => File["${alvi_dir}/app/console"],
        content => template('alvi/alvi-heartbeat.conf.erb'),
        notify  => Service["alvi-heartbeat"];
    }

    service {
        "alvi-heartbeat":
            hasstatus => true,
            hasrestart => true,
            ensure => running,
            enable => true,
            require => File["/etc/init/alvi-heartbeat.conf"];
    }
}
