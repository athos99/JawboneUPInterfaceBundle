<?php
/**
 *
 * Error Codes: 701 -
 */
namespace AG\JawboneUPInterfaceBundle\Jawbone;

use Symfony\Component\Stopwatch\Stopwatch;
use AG\JawboneUPInterfaceBundle\Jawbone\Exception as JawboneException;

/**
 * Class SleepGateway
 *
 * @package AG\JawboneUPInterfaceBundle\Jawbone
 *
 * @since 0.0.1
 */
class SleepGateway extends EndpointGateway
{
    /**
     * Get the list of sleeps of the current user
     *
     * @access public
     * @version 0.0.1
     *
     * @param  \DateTime  $date
     * @param  \DateTime  $startDate
     * @param  \DateTime  $endDate
     * @throws JawboneException
     * @return object The result as an object
     */
    public function getSleeps(\DateTime $date = null, \DateTime $startDate = null, \DateTime $endDate = null)
    {
        /** @var Stopwatch $timer */
        $timer = new Stopwatch();
        $timer->start('Get Sleeps', 'Jawbone UP API');

        $body = array();
        if (!is_null($date))
        {
            $body['date'] = $date->format("Ymd");
        }
        else {
            if (!is_null($startDate))
            {
                $body['start_time'] = $startDate->getTimestamp();
            }
            if (!is_null($endDate))
            {
                $body['end_time'] = $endDate->getTimestamp();
            }
        }

        try
        {
            /** @var object $sleeps */
            $sleeps = $this->makeApiRequest('/users/@me/sleeps', 'GET', $body);
            $timer->stop('Get Sleeps');
            return $sleeps;
        }
        catch (\Exception $e)
        {
            $timer->stop('Get Sleeps');
            throw new JawboneException('Unable to get sleeps for the user.', 701, $e);
        }
    }
}
