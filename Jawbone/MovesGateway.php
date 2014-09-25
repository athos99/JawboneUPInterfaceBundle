<?php
/**
 *
 * Error Codes: 601 -
 */
namespace AG\JawboneUPInterfaceBundle\Jawbone;

use Symfony\Component\Stopwatch\Stopwatch;
use AG\JawboneUPInterfaceBundle\Jawbone\Exception as JawboneException;

/**
 * Class MovesGateway
 *
 * @package AG\JawboneUPInterfaceBundle\Jawbone
 *
 * @since 0.0.1
 */
class MovesGateway extends EndpointGateway
{
    /**
     * Get the list of moves of the current user
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
    public function getActivities(\DateTime $date = null, \DateTime $startDate = null, \DateTime $endDate = null)
    {
        /** @var Stopwatch $timer */
        $timer = new Stopwatch();
        $timer->start('Get Moves', 'Jawbone UP API');

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
            /** @var object $moves */
            $moves = $this->makeApiRequest('/users/@me/moves', 'GET', $body);
            $timer->stop('Get Moves');
            return $moves;
        }
        catch (\Exception $e)
        {
            $timer->stop('Get Moves');
            throw new JawboneException('Unable to get a list of moves.', 601, $e);
        }
    }
}
