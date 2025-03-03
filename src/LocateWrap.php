<?php

namespace Takuya\PhpPlocateWrapper;

use Takuya\ProcOpen\ProcOpen;
use Takuya\SystemUtil\Stream\StringIO\StringIO;

class LocateWrap {
  
  public function __construct( protected $locatedb = null ) {
    cmd_exists('plocate') === false && throw new \RuntimeException('plocate not found.');
  }
  
  public function search( $keyword="''", $regexp = false ):\Generator {
    $cmd = array_filter([
                          'plocate',
                          $this->locatedb ? "-d" : '',
                          $this->locatedb ?? '',
                          $regexp ? '-r' : '',
                          $keyword,
                        ]);
    $proc = new ProcOpen($cmd);
    $proc->enableBuffering();
    $proc->run();
    $ret = $proc->getOutput();
    $sio = new StringIO($ret);
    
    return $sio->lines();
  }
}