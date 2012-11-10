class alvi::workerstatus($alvi_dir = '/data') {
    
    file {"${alvi_dir}/app/console":
        ensure => file
    }
    
    file {'/etc/init/alvi-workerstatus.conf':
        ensure  => file,
        owner   => root,
        group   => root,
        mode    => '0644',
        require => File["${alvi_dir}/app/console"],
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
