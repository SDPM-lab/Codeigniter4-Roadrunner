<?php

namespace SDPMlab\Ci4Roadrunner\Debug;

use CodeIgniter\CodeIgniter;
use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Debug\Toolbar\Collectors\History;
use CodeIgniter\Format\JSONFormatter;
use CodeIgniter\Format\XMLFormatter;
use Config\Services;
use Laminas\Diactoros\Stream;
use Psr\Http\Message\StreamInterface;

/**
 * Debug Toolbar
 *
 * Displays a toolbar with bits of stats to aid a developer in debugging.
 *
 * Inspiration: http://prophiler.fabfuel.de
 *
 * @package CodeIgniter\Debug
 */
class Toolbar
{

	/**
	 * Toolbar configuration settings.
	 *
	 * @var BaseConfig
	 */
	protected $config;

	/**
	 * Collectors to be used and displayed.
	 *
	 * @var \CodeIgniter\Debug\Toolbar\Collectors\BaseCollector[]
	 */
	protected $collectors = [];

	/**
	 * The incoming request.
	 *
	 * @var \CodeIgniter\HTTP\IncomingRequest
	 */
	protected $request;
	//--------------------------------------------------------------------

	/**
	 * The outgoing response.
	 *
	 * @var \CodeIgniter\HTTP\Response
	 */
	protected $response;

	/**
	 * Constructor
	 *
	 * @param BaseConfig $config
	 */
	public function __construct(
		BaseConfig $config,
		\CodeIgniter\HTTP\IncomingRequest $request
	)
	{
		$this->config = $config;
		$this->request = $request;
		foreach ($config->collectors as $collector)
		{
			if (! class_exists($collector))
			{
				log_message('critical', 'Toolbar collector does not exists(' . $collector . ').' .
						'please check $collectors in the Config\Toolbar.php file.');
				continue;
			}

			$this->collectors[] = new $collector();
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Inject debug toolbar into the response.
	 */
	public function respond()
	{
		if (ENVIRONMENT === 'testing')
		{
			return false;
		}

		// @codeCoverageIgnoreStart
		$request = $this->request;

		// If the request contains '?debugbar then we're
		// simply returning the loading script
		if ($request->getGet('debugbar') !== null)
		{
			ob_start();
			include($this->config->viewsPath . 'toolbarloader.js.php');
			$output = ob_get_clean();
			return $this->getResponse($output,200,"application/javascript");
		}

		// Otherwise, if it includes ?debugbar_time, then
		// we should return the entire debugbar.
		if ($request->getGet('debugbar_time'))
		{
			helper('security');

			// Negotiate the content-type to format the output
			$format = $request->negotiate('media', [
				'text/html',
				'application/json',
				'application/xml',
			]);
			$formatName = explode('/', $format)[1];

			$file     = sanitize_filename('debugbar_' . $request->getGet('debugbar_time'));
			$filename = WRITEPATH . 'debugbar/' . $file . '.json';

			// Show the toolbar
			if (is_file($filename))
			{
				$contents = $this->format(file_get_contents($filename), $formatName);
				return $this->getResponse($contents,200,$format);
			}

			// File was not written or do not exists
			return $this->getResponse("",404,"text/html");
		}
		return false;
		// @codeCoverageIgnoreEnd
	}

	/**
	 * Format output
	 *
	 * @param string $data   JSON encoded Toolbar data
	 * @param string $format html, json, xml
	 *
	 * @return string
	 */
	protected function format(string $data, string $format = 'html'): string
	{
		$data = json_decode($data, true);

		if ($this->config->maxHistory !== 0)
		{
			$history = new History();
			$history->setFiles(
				$this->request->getGet('debugbar_time'),
				$this->config->maxHistory
			);

			$data['collectors'][] = $history->getAsArray();
		}

		$output = '';

		switch ($format)
		{
			case 'html':
				$data['styles'] = [];
				extract($data);
				$parser = Services::parser($this->config->viewsPath, null, false);
				ob_start();
				include($this->config->viewsPath . 'toolbar.tpl.php');
				$output = ob_get_clean();
				break;
			case 'json':
				$formatter = new JSONFormatter();
				$output    = $formatter->format($data);
				break;
			case 'xml':
				$formatter = new XMLFormatter;
				$output    = $formatter->format($data);
				break;
		}

		return $output;
	}

	private function getResponse(string $body,int $code = 200,string $contentType){
		return new \Laminas\Diactoros\Response(
			$this->createBody($body),
			$code,
			["Content-Type" => $contentType]
		);
	}

	private function createBody(string $bodyStr) : StreamInterface
    {
        $html = $bodyStr;
        if ($html instanceof StreamInterface){
            return $html;
        }
        $body = new Stream('php://temp', 'wb+');
        $body->write($html);
        $body->rewind();
        return $body;
	}
	
	/**
	 * Called within the view to display the timeline itself.
	 *
	 * @param array   $collectors
	 * @param float   $startTime
	 * @param integer $segmentCount
	 * @param integer $segmentDuration
	 * @param array   $styles
	 *
	 * @return string
	 */
	protected function renderTimeline(array $collectors, float $startTime, int $segmentCount, int $segmentDuration, array &$styles): string
	{
		$displayTime = $segmentCount * $segmentDuration;
		$rows        = $this->collectTimelineData($collectors);
		$output      = '';
		$styleCount  = 0;

		foreach ($rows as $row)
		{
			$output .= '<tr>';
			$output .= "<td>{$row['name']}</td>";
			$output .= "<td>{$row['component']}</td>";
			$output .= "<td class='debug-bar-alignRight'>" . number_format($row['duration'] * 1000, 2) . ' ms</td>';
			$output .= "<td class='debug-bar-noverflow' colspan='{$segmentCount}'>";

			$offset = ((((float) $row['start'] - $startTime) * 1000) / $displayTime) * 100;
			$length = (((float) $row['duration'] * 1000) / $displayTime) * 100;

			$styles['debug-bar-timeline-' . $styleCount] = "left: {$offset}%; width: {$length}%;";
			$output                                     .= "<span class='timer debug-bar-timeline-{$styleCount}' title='" . number_format($length, 2) . "%'></span>";
			$output                                     .= '</td>';
			$output                                     .= '</tr>';

			$styleCount ++;
		}

		return $output;
	}

	/**
	 * Returns a sorted array of timeline data arrays from the collectors.
	 *
	 * @param array $collectors
	 *
	 * @return array
	 */
	protected function collectTimelineData($collectors): array
	{
		$data = [];

		// Collect it
		foreach ($collectors as $collector)
		{
			if (! $collector['hasTimelineData'])
			{
				continue;
			}

			$data = array_merge($data, $collector['timelineData']);
		}

		// Sort it

		return $data;
	}

}
