include php
include phpzookeeper
include alvi::worker

# PHP Extensions
php::module { ['xdebug', 'curl'] :
}
