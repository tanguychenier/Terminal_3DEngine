<?php
/**
 * En attente d'études: T. CHENIER
 */
 
if (!defined('__CLASS_MOTEUR_3D__'))
{
	define('__CLASS_MOTEUR_3D__', 0.06);
	
	require_once(dirname(__FILE__).'/matrix.class.php');
	require_once(dirname(__FILE__).'/light.class.php');
	require_once(dirname(__FILE__).'/objet.class.php');

	define('PI_180', M_PI/180.0);

	class moteur_3D
	{
		protected	$timer		= null;
		protected	$background	= array('r' => 0, 'v' => 0, 'b' => 0);
		protected	$m_vue		= null;
		public		$img		= null;
		public		$scr_width	= 0;
		public		$scr_height	= 0;
		public		$real_width	= 0;
		public		$real_height = 0;
		public		$ouverture	= 0.0;	
		public		$view_xMin	= 0.0;
		public		$view_xMax	= 0.0;
		public		$view_yMin	= 0.0;
		public		$view_yMax	= 0.0;
		public		$light_lst	= array();
		public		$light_amb	= array(0, 0, 0);
		public		$zBuffer	= array();
		public		$z_near		= 0;
		public		$z_far		= 0;
		public		$z_def		= 1000;
				
		public function __construct()
		{
			set_time_limit(120);
			$this->setTimer();

			// matrice de vue
			$this->m_vue = new Matrix();
			$this->matrixIdentity();
		}

		public function __destruct()
		{

		}
		
		public function setScreen($width, $height)
		{
			$f = 1.5;
			
			$this->scr_width	= floor($width*$f);
			$this->scr_height	= floor($height*$f);

			$this->real_width	= $width;
			$this->real_height	= $height;

			return $this;
		}
		
		public function setBackground($r, $v, $b)
		{
			$this->background['r'] = $r;
			$this->background['v'] = $v;
			$this->background['b'] = $b;
			return $this;
		}

		public function lightAdd($pos, $color, $length)
		{
			$this->light_lst[] = new Light($this->m_vue->MultiplicationPos($pos), $color, $length);
			return $this;
		}
		
		public function lightAmbiant($color)
		{
			$this->light_amb = $color;
			return $this;
		}
		
		public function setOuverture($angle_ouverture)
		{
			$this->ouverture		= PI_180*$angle_ouverture;	
			return $this;
		}
		
		public function setView($xMin, $xMax, $yMin, $yMax)
		{
			$this->view_xMin = $xMin;
			$this->view_xMax = $xMax;
			$this->view_yMin = $yMin;
			$this->view_yMax = $yMax;
			return $this;
		}
		
		public function matrixIdentity()
		{
			$this->m_vue->Identite();
			return $this;
		}
		
		public function matrixPush()
		{
			$this->m_vue->Push();
			return $this;
		}

		public function matrixPop()
		{
			$this->m_vue->Pop();
			return $this;
		}
		
		public function matrixTranslate($vx, $vy, $vz)
		{
			$m = new Matrix();
			$this->m_vue->Multiplication($m->Translation($vx, $vy, $vz));
			return $this;
		}
		
		public function matrixRotateX($rx)
		{
			$m = new Matrix();
			$this->m_vue->Multiplication($m->RotationX(PI_180*$rx));
			return $this;
		}

		public function matrixRotateY($ry)
		{
			$m = new Matrix();
			$this->m_vue->Multiplication($m->RotationY(PI_180*$ry));
			return $this;
		}

		public function matrixRotateZ($rz)
		{
			$m = new Matrix();
			$this->m_vue->Multiplication($m->RotationZ(PI_180*$rz));
			return $this;
		}
		
		public function matrixScale($sx, $sy, $sz)
		{
			$m = new Matrix();
			$this->m_vue->Multiplication($m->Scale($sx, $sy, $sz));
			return $this;
		}
		
		public function ZbufInit($near = 1, $far = 80, $def = 1000)
		{
			$this->z_near	= $near;
			$this->z_far	= $far;
			$this->z_def	= $def;

			$this->zBuffer = array_fill(0, $this->scr_width, array_fill(0, $this->scr_height, $this->z_def));
		}
		
		public function ZbufSet($x, $y, $z)
		{
			if ($x<0) return false;
			if ($y<0) return false;
			if ($x>$this->scr_width-1) return false;
			if ($y>$this->scr_height-1) return false;
			
			
			$z = floor($this->z_def*($z-$this->z_near)/($this->z_far-$this->z_near));
			if ($z<0) return false;
			if ($z>$this->z_def) return false;

			if ($this->zBuffer[$x][$y]<$z) return false;
			
			$this->zBuffer[$x][$y]=$z;
			return true;
		}
		
		public function drawInit()
		{
			$this->img = imagecreatetruecolor($this->scr_width, $this->scr_height);
			$background = imagecolorallocate($this->img, $this->background['r'], $this->background['v'], $this->background['b']);
			imagefilledrectangle($this->img, 0, 0, $this->scr_width, $this->scr_height, $background);
			return $this;
		}
		
		public function drawObject($obj)
		{
			$obj->ptTransform($this->m_vue);
			$obj->ptProjection($this);
			$obj->fcPrepare();
			$obj->fcDraw($this);
			return $this;
		}
		
		public function drawFinish($generate = false, $quality=75)
		{
			$tmp = imagecreatetruecolor($this->real_width, $this->real_height);
			imagecopyresampled($tmp, $this->img, 0, 0, 0, 0, $this->real_width, $this->real_height, $this->scr_width, $this->scr_height);

			if ($generate)
			{
				$fonte = 2;
				$txt = 'v'.__CLASS_MOTEUR_3D__.' - generate in : '.number_format($this->getTimer()*1000, 1, '.', '').' ms';
				$white = imagecolorallocate($tmp, 255, 255, 255);
				$x = $this->real_width-imagefontwidth($fonte)*strlen($txt)-2;
				$y = $this->real_height-imagefontheight($fonte)-2;
				imagestring($tmp, $fonte, $x, $y, $txt, $white);
			}
			
			header('Content-type: image/jpg');
			imagejpeg($tmp, null, $quality);
			imagedestroy($this->img);
			imagedestroy($tmp);
		}
		
		protected function setTimer()
		{
			list($usec, $sec) = explode(" ", microtime());
			$this->timer = (float)$sec + (float)$usec; 
		}
		
		protected function getTimer()
		{
			list($usec, $sec) = explode(" ", microtime());

			$timer = (float)$sec + (float)$usec;
			
			return $timer - $this->timer;		
		}
	}
}
