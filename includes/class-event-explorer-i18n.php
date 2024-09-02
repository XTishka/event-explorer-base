<?php

// includes/class-event-explorer-i18n.php

class Event_Explorer_i18n
{
	public function load_plugin_textdomain()
	{
		load_plugin_textdomain(
			'event-explorer',
			false,
			dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
		);
	}
}
