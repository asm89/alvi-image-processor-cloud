class phpstats() {
    Exec {
        path => '/bin:/usr/bin:/usr/sbin',
    }

    exec {
        "Install 'stats' with pecl":
            command => "pecl install http://pecl.php.net/get/stats-1.0.2.tgz",
            creates => "/usr/lib/php5/20090626/stats.so";
    }

    file {'/etc/php5/conf.d/stats.ini':
        ensure  => file,
        owner   => root,
        group   => root,
        mode    => '0644',
        content => template('phpstats/stats.ini.erb');
    }
}
