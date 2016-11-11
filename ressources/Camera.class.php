<?PHP
require_once 'Vertex.class.php';
require_once 'Vector.class.php';
require_once 'Matrix.class.php';
Class Camera
{
	public $preset = array(array(1.00, 0.00, 0.00, 0.00),
						   array(0.00, 1.00, 0.00, 0.00),
						   array(0.00, 0.00, 1.00, 0.00),
						   array(0.00, 0.00, 0.00, 1.00));
	private $_tT;
	private $_tR;
	private $_vM;
	private $_P;
	private $_origin;
	private $_ratio;
	private $_fov;
	private $_width;
	private $_height;
	public static $verbose = false;
	public function __construct(array $kwargs)
	{
		$this->_origin = $kwargs['origin'];
		$orientation = $kwargs['orientation'];
		if (array_key_exists('ratio', $kwargs))
		{
			$this->_ratio = $kwargs['ratio'];
			$this->_width = 1000;
			$this->_height = $this->_width / $this->_ratio;
		}
		else
		{
			$this->_width = $kwargs['width'];
			$this->_height = $kwargs['height'];
			$this->_ratio = $this->_width / $this->_height;
		}
		$this->_fov = $kwargs['fov'];
		$near = $kwargs['near'];
		$far = $kwargs['far'];
		$new_vector = new Vector(array('dest' => $this->_origin));
		$origin_neg = $new_vector->opposite();
		$this->_tT = new Matrix(array('preset' => Matrix::TRANSLATION, 'vtc' => $origin_neg));
		$this->_tR = $this->_transpose($orientation);
		$this->_vM = $this->_tR->mult($this->_tT);
		$this->_P = new Matrix(array('preset' => Matrix::PROJECTION, 'ratio' => $this->_ratio, 'fov' => $this->_fov, 'near' => $near, 'far' => $far));
		if (self::$verbose == true)
		{
			print ("Camera instance constructed\n");
		}
	}
	public function __destruct()
	{
		if (self::$verbose == TRUE)
		{
			print("Camera instance destructed\n");
			return ;
		}
	}
	public function __toString()
	{
		return "Camera(\n"
				. "+ Origine: " . $this->_origin . "\n"
				. "+ tT:\n"
				. $this->_tT . "\n"
				. "+ tR:\n"
				. $this->_tR . "\n"
				. "+ tR->mult( tT ):\n"
				. $this->_vM . "\n"
				. "+ Proj:\n"
				. $this->_P . "\n"
				. ")";
	}
	private function _transpose(Matrix $mat)
	{
		$transpose = array(array(0, 0, 0, 0),
						   array(0, 0, 0, 0),
						   array(0, 0, 0, 0),
						   array(0, 0, 0, 0));
		$i = 0;
		while ($i < 4)
		{
			$j = 0;
			while ($j < 4)
			{
				$transpose[$j][$i] = $mat->preset[$i][$j];
				++$j;
			}
			++$i;
		}
		$mat->preset = $transpose;
		return ($mat);
	}
	public function watchVertex(Vertex $worldVertex)
	{
		$vectlocal = new Vector(array('dist' => $worldVertex));
		$vect_world = $this->_origin->add($vectlocal);
		$new_vertex = new Vertex(array('x' => $vect_world->getX(), 'y' => $vect_world->getY(), 'z' => $vect_world->getZ()));
		$new_vertex = $this->_vM->transformVertex($new_vertex);
		$new_vertex = $this->_P->transformVertex($new_vertex);
		$new_vertex = new Vertex(array('x' => (2 * (($new_vertex->getX() + 0.5) * $this->_width) - 1) * deg2rad($this->_fov) * $this->_ratio,
									   'y' => (1 - 2 * (($new_vertex->getY() + 0.5) * $this->_height)) * deg2rad($this->_fov),
									   'z' => -1));
		return ($new_vertex);
	}
	public static function doc()
	{
		return (file_get_contents("Camera.doc.txt"));
	}
}
?>
