include php
include stdlib

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

Class['rabbitmq::repo::apt'] -> Class['rabbitmq::server']
