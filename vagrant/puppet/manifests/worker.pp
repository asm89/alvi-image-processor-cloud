include php
include phpzookeeper
include phpstats
include alvi::worker

# PHP Extensions
php::module { ['xdebug', 'curl'] :
}
