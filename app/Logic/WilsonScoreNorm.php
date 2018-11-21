<?php
/**
 * @author: wanghui
 * @date: 2018/11/21 上午11:49
 * @email:    hank.HuiWang@gmail.com
 */

namespace App\Logic;

/**
 * Class WilsonScoreNorm
 * https://stackoverrun.com/cn/q/2192534
 * http://www.goproblems.com/test/wilson/wilson.php?v1=0&v2=0&v3=0&v4=1&v5=1
 * https://www.jianshu.com/p/4d2b45918958
 * @package App\Logic
 */
class WilsonScoreNorm
{
    protected static $instance = null;
    protected $confidence; // Level of Confidence
    protected $n; // Count of Total results
    protected $p; // Positive results out of total results
    protected $z; // Quantile of the standard normal distribution

    public static function instance($average, $total, $confidence = 0.95){
        if(!self::$instance){
            self::$instance = new self();
        }
        if (self::$instance->confidence != $confidence) {
            self::$instance->z = self::$instance->pnorm(1 - (1 - $confidence) / 2);
        }
        self::$instance->init($average, $total, $confidence);
        return self::$instance;
    }

    /**
     * WilsonScoreNorm constructor.
     * @param $average
     * @param $total
     * @param float $confidence
     */
    public function __construct(){

    }

    /**
     * @return float|int
     */
    public function getLowerBound(){ return $this->n > 0 ? $this->multiplier() * ($this->innerLeft() - $this->innerRight()) : 0; }
    /**
     * @return float|int
     */
    public function getUpperBound(){ return $this->n > 0 ? $this->multiplier() * ($this->innerLeft() + $this->innerRight()) : 0; }
    /**
     * @return float|int
     */
    public function score(){ return 1 + 4 * $this->getLowerBound(); }
    /**
     * @return array
     */
    public function getInterval(){ return array(0 => $this->getLowerBound(), 1 => $this->getUpperBound()); }
    /**
     * @link https://github.com/abscondment/statistics2/blob/master/lib/statistics2/base.rb#L89
     * @param $qn
     * @return float
     */
    protected function pnorm($qn){
        $b = array(
            1.570796288, 0.03706987906, -0.8364353589e-3, -0.2250947176e-3,
            0.6841218299e-5, 0.5824238515e-5, -0.104527497e-5, 0.8360937017e-7,
            -0.3231081277e-8, 0.3657763036e-10, 0.6936233982e-12
        );
        if ($qn < 0.0 || $qn > 1.0 || $qn == 0.5) {
            return 0.0;
        }
        $w1 = $qn > 0.5 ? 1.0 - $qn : $qn;
        $w3 = - log(4.0 * $w1 * (1.0 - $w1));
        $w1 = $b[0];
        for ($i = 1; $i <= 10; $i++) {
            $w1 += $b[$i] * pow($w3, $i);
        }
        return $qn > 0.5 ? sqrt($w1 * $w3) : -sqrt($w1 * $w3);
    }

    private function init($average, $total, $confidence = 0.95) {
        $this->confidence = floatval($confidence);
        $this->n = $total;
        $this->p = ($average - 1) / 4;
    }

    /**
     * @return float
     */
    private function multiplier(){ return 1 / (1 + (pow($this->z, 2) / $this->n)); }
    /**
     * @return float
     */
    private function innerLeft(){ return $this->p + (pow($this->z, 2) / (2 * $this->n)); }
    /**
     * @return float
     */
    private function innerRight(){ return $this->z * sqrt(($this->p * (1 - $this->p) / $this->n) + (pow($this->z, 2) / (4 * pow($this->n, 2)))); }
}