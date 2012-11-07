include php
include phpzookeeper
include phpstats
include alvi::worker
include alvi::heartbeat

# PHP Extensions
php::module { ['xdebug', 'curl'] :
}
