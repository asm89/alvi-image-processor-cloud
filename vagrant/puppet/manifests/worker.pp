include php
include alvi::worker
include alvi::heartbeat

# PHP Extensions
php::module { ['xdebug', 'curl'] :
}
