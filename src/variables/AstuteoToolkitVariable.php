<?php
namespace astuteo\astuteotoolkit\variables;
use astuteo\astuteotoolkit\services\LocationService;
use astuteo\astuteotoolkit\services\ToolkitService;
use astuteo\astuteotoolkit\services\TransformService;
use Craft;
use craft\elements\Entry;
use craft\helpers\DateTimeHelper;

class AstuteoToolkitVariable
{
    /**
     * @param $name
     * @param bool $default
     * @return bool|mixed
     */
    public function unsecureCookie($name, $default = false) {
        if(!isset($_COOKIE[$name])) {
            return $default;
        } else {
            return $_COOKIE[$name];
        }
    }

    public function imgixTransformMap($image, $options, $serviceOptions) {
        return (new TransformService)->imgix($image,$options,$serviceOptions);
    }

	/**
	 * @return array
	 */
    public function countries()
    {
		return LocationService::countries();
    }

	/**
	 * @return array
	 */
    public function states() {
		return LocationService::states();
    }


    public function testUpdates() {
        $message = 'Up-to-date';
        $totalupdates = Craft::$app->getUpdates()->getTotalAvailableUpdates();
        if($totalupdates != 0) {
            $message = $totalupdates . ' Updates';
        }
        if(Craft::$app->getUpdates()->getIsCriticalUpdateAvailable()) {
            $message = 'CRITICAL UPDATE ' . $message;
        }
        return $message;
    }
	/**
	 * @return array
	 */
    public function provinces() {
		return LocationService::provinces();
    }

    /**
	 * Returns true or false if user client supports
	 * webp.
	 * @return bool
	 */
	public function clientSupportsWebp(): bool
	{
		$request = Craft::$app->getRequest();
		return $request->accepts('image/webp');
	}

	/**
	 * Checks for webp support in image driver
	 *
	 * @return bool
	 */
	public function serverSupportsWebp(): bool
	{
		return ToolkitService::hasSupportForWebP();
	}

    // Standardized way to pull future events.
    // Assumptions made:
    // End date field handle is "endDate"
    // Start date field handle is "startDate"
    public function futureEvents($options = null) {
        if(isset($options['limit'])) {
            $limit = $options['limit'];
        } else {
            $limit = 10;
        }
        if(isset($options['section'])) {
            $section = $options['section'];
        } else {
            $section = 'events';
        }

        $events = Entry::find()
            ->section($section)
            ->orderBy('startDate asc')
            ->all();

        try {
            $now = DateTimeHelper::toDateTime(DateTimeHelper::currentTimeStamp())->format('Ymd');
        } catch (\Exception $e) {
        }
        $futureEntries = array();

        foreach ($events as $event) {
            if ( !empty($event->endDate) ) {
                // if end date set let's use that to compare
                $compareDate = DateTimeHelper::toDateTime( $event->endDate)->format('Ymd');
            } elseif(!empty($event->startDate)) {
                // otherwise let's use the start date
                $compareDate = DateTimeHelper::toDateTime( $event->startDate)->format('Ymd');
            } else {
                return null;
            }
            // now let's see if that's today or in the future, and if so merge the IDs
            if( $compareDate >= $now ) {
                $futureEntries[] = $event->id;
            }
        }

        return Entry::find()
            ->section($section)
            ->id($futureEntries)
            ->orderBy('startDate asc')
            ->limit($limit)
            ->all();
    }
}
