class alvi::worker($alvi_dir = '/data') {

    file {"${alvi_dir}/app/console":
        ensure => file
    }

    file {'/etc/init/alvi-worker.conf':
        ensure  => file,
        owner   => root,
        group   => root,
        mode    => '0644',
        require => File["${alvi_dir}/app/console"],
        content => template('alvi/alvi-worker.conf.erb'),
        notify  => Service["alvi-worker"];
    }

    service {
        "alvi-worker":
            hasstatus => true,
            hasrestart => true,
            ensure => running,
            enable => true,
            require => File["/etc/init/alvi-worker.conf"];
    }
}
