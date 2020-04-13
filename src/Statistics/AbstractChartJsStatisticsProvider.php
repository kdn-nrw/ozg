<?php

namespace App\Statistics;

/**
 * Abstract provider class for chart.js statistics providers
 * @see http://www.chartjs.org
 */
abstract class AbstractChartJsStatisticsProvider extends AbstractStatisticsProvider implements ChartStatisticsProviderInterface
{
    /**
     * The chart type
     * @var string
     */
    protected $chartType = 'bar';

    /**
     * Title options
     *
     * @see http://www.chartjs.org/docs/latest/configuration/title.html
     *
     * @var array
     */
    protected $titleOptions = [
        'display' => false,
    ];

    /**
     * Legend options
     *
     * @see http://www.chartjs.org/docs/latest/configuration/legend.html
     * @var array
     */
    protected $legendOptions = [
        //'display' => false,
    ];

    /**
     * Tooltips options
     *
     * @see http://www.chartjs.org/docs/latest/configuration/tooltip.html
     * @var array
     */
    protected $tooltipsOptions = [];

    /**
     * Scales options
     *
     * @var array
     */
    protected $scalesOptions = [];


    /**
     * The chart options
     *
     * @var array
     */
    private $chartJsOptions = [
        'responsive' => true,
    ];

    private $availablePlugins = [
        // https://chartjs-plugin-datalabels.netlify.com/options.html
        'datalabels' => '/vendor/chartjs-plugin-datalabels/dist/chartjs-plugin-datalabels.min.js',
    ];
    /**
     * @var array
     */
    private $scripts = [
        'core' => '/vendor/chart.js/dist/Chart.bundle.min.js',
    ];

    /**
     * https://nagix.github.io/chartjs-plugin-colorschemes
     * @var array
     */
    protected $colors = ['#8dd3c7', '#ffffb3', '#bebada', '#fb8072', '#80b1d3', '#fdb462', '#b3de69', '#fccde5', '#d9d9d9', '#bc80bd', '#ccebc5', '#ffed6f'];

    /**
     * Create the chart data used in the chart configuration
     *
     * @return array
     */
    abstract protected function createChartData();

    /**
     * Adds the given plugin
     * Has to be called in the constructor, dos NOT work when called in createChartData because
     * the scripts must be added before the data are generated (output order in Twig templates!)
     *
     * @param string $name
     * @param array $options
     */
    protected function addPlugin($name, $options)
    {
        $this->addSectionOptions('plugins', $options, $name);
        if (isset($this->availablePlugins[$name])) {
            $this->scripts[$name] = $this->availablePlugins[$name];
        }
    }

    /**
     * Disable the chart legend
     */
    final protected function disableLegend()
    {
        $this->legendOptions = [
            'display' => false,
        ];
    }

    /**
     * Adds the options for the given key in the given section; existing options for the same key will be overwritten
     * If no key is given, the entire section options are overwritten
     *
     * @param string $section
     * @param string $key
     * @param array $options
     */
    private function addSectionOptions($section, $options, $key = null)
    {
        if (!empty($key)) {
            if (!isset($this->chartJsOptions[$section])) {
                $this->chartJsOptions[$section] = [];
            }
            $this->chartJsOptions[$section][$key] = $options;
        } else {
            $this->chartJsOptions[$section] = $options;
        }
    }


    /**
     * Returns the provider chart options
     *
     * @return array
     */
    protected function getChartOptions() {
        $this->addSectionOptions('title', $this->titleOptions);
        if (!empty($this->legendOptions)) {
            $this->addSectionOptions('legend', $this->legendOptions);
        }
        if (!empty($this->tooltipsOptions)) {
            $this->addSectionOptions('tooltips', $this->tooltipsOptions);
        }
        if (!empty($this->scalesOptions)) {
            $this->addSectionOptions('scales', $this->scalesOptions);
        }
        return $this->chartJsOptions;
    }

    /**
     * Returns the chart configuration
     *
     * @return array
     */
    public function getChartConfig()
    {
        // Get data first in case the options are overridden depending on the loaded data!
        $data = $this->createChartData();
        // Get options AFTER loading the data
        $options = $this->getChartOptions();
        return [
            'type' => $this->chartType,
            'data' => $data,
            'options' => $options,
        ];
    }

    public function getScripts()
    {
        return $this->scripts;
    }
}