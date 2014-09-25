<?php
/**
 *
 * Error Codes: 501 -
 */
namespace AG\JawboneUPInterfaceBundle\Jawbone;

use Symfony\Component\Stopwatch\Stopwatch;
use AG\JawboneUPInterfaceBundle\Jawbone\Exception as JawboneException;

/**
 * Class ActivityGateway
 *
 * @package AG\JawboneUPInterfaceBundle\Jawbone
 *
 * @since 0.0.1
 */
class GoalGateway extends EndpointGateway
{
    /**
     * Get the goals the user has set for UP.
     *
     * @access public
     * @version 0.0.1
     *
     * @throws JawboneException
     * @return object The result as an object
     */
    public function getGoals()
    {
        /** @var Stopwatch $timer */
        $timer = new Stopwatch();
        $timer->start('Get Goals', 'Jawbone UP API');

        try
        {
            /** @var object $goals */
            $goals = $this->makeApiRequest('/users/@me/goals', 'GET', $body);
            $timer->stop('Get Goals');
            return $goals;
        }
        catch (\Exception $e)
        {
            $timer->stop('Get Goals');
            throw new JawboneException('Unable to get goals for the user.', 501, $e);
        }
    }
}
