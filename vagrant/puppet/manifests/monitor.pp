include php
include stdlib
include graphite
include phpzookeeper

# PHP Extensions
php::module { ['xdebug', 'curl'] :
}

class {'statsd':
    graphite_host => localhost,
    flush_interval => 1000
}
