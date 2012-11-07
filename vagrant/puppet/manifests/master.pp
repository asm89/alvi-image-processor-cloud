include php
include stdlib
include graphite
include zookeeper
include phpzookeeper
include phpstats
include alvi::queuestats

class { 'apt':
  always_apt_update => false
}

# PHP Extensions
php::module { ['xdebug', 'curl'] :
}

# add apt repo for rabbitmq
class { 'rabbitmq::repo::apt':
  pin    => 900
}

# install rabbitmq
class { 'rabbitmq::server':
  port              => '5673',
  delete_guest_user => false
}

rabbitmq_plugin {'rabbitmq_management':
  ensure => present,
  provider => 'rabbitmqplugins',
  notify => Service["rabbitmq-server"],
}

Class['rabbitmq::repo::apt'] -> Class['rabbitmq::server']

class {'statsd':
    graphite_host => localhost,
    flush_interval => 1000
}
