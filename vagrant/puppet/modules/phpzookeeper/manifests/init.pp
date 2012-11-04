class phpzookeeper() {

    package { 'libzookeeper-mt-dev':
        ensure => present;
    }

    package { 'php-pear':
        ensure => present;
    }

    package { 'make':
        ensure => present;
    }

    Exec {
        path => '/bin:/usr/bin:/usr/sbin',
    }

    exec {
        "Install 'php-zookeeper' with pecl":
            require => Package['php-pear'],
            command => "pecl install zookeeper-0.2.1",
            creates => "/usr/lib/php5/20090626/zookeeper.so";
    }

    file {'/etc/php5/conf.d/zookeeper.ini':
        ensure  => file,
        owner   => root,
        group   => root,
        mode    => '0644',
        require => Package['libzookeeper-mt-dev'],
        content => template('phpzookeeper/zookeeper.ini.erb');
    }
}
