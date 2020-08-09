<?php

namespace Quince\PersianGD;

use Quince\PersianGD\Contracts\GDTool as GDToolContract;
use Quince\PersianGD\Contracts\StringDecorator;
use Quince\PersianGD\Exceptions\PersianGDException;

class GDTool implements GDToolContract
{
    /**
     * The canvas width.
     *
     * @var int
     */
    protected $width = 500;

    /**
     * Output file name.
     *
     * @var string
     */
    protected $fileName;

    /**
     * Flag for saving image or output.
     *
     * @var bool
     */
    protected $outputImage = false;

    /**
     * Image background hexadecimal color code.
     *
     * @var string
     */
    protected $backgroundColor = '#FFFFFF';

    /**
     * Image background color allocated code.
     *
     * @var int
     */
    protected $backgroundColorAllocate;

    /**
     * Image font hexadecimal color code.
     *
     * @var string
     */
    protected $fontColor = '#000000';

    /**
     * Image font color allocated code.
     *
     * @var int
     */
    protected $fontColorAllocate;

    /**
     * Image font size in point.
     *
     * @var int
     */
    protected $fontSize = 12;

    /**
     * Text angle in degrees.
     *
     * @var int
     */
    protected $angle = 0;

    /**
     * The coordinates of the first character.
     *
     * @var int
     */
    protected $horizontalPosition = 10;

    /**
     *  The coordinates of the first character.
     *
     * @var int
     */
    protected $verticalPosition = 10;

    /**
     * Line height of each line.
     *
     * @var int
     */
    protected $lineHeight = 25;

    /**
     * The font to be used to generate strings.
     *
     * @var string
     */
    protected $font;

    /**
     * Array of lines to generated.
     *
     * @var array
     */
    protected $lines = [];

    /**
     * GD image resource.
     *
     * @var resource
     */
    protected $imageResource;

    /**
     * The decorator to decorate strings before print them into image.
     *
     * @var StringDecorator
     */
    protected $decorator;

    /**
     * Whether using local (Persian) numeric character or not .
     *
     * @var bool
     */
    protected $useLocalNumber = true;

    /**
     * The GDTool constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->setOptions($options);
    }

    /**
     * Sets the image canvas width.
     *
     * @param int $width
     *
     * @return GDTool
     */
    public function setWidth($width)
    {
        $this->width = $width;

        return $this;
    }

    /**
     * Sets the name of output image file.
     *
     * @param string $fileName
     *
     * @return GDTool
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * Sets the condition of saving or outputting image.
     *
     * @param bool $outputImage
     *
     * @return GDTool
     */
    public function setOutputImage($outputImage)
    {
        $this->outputImage = $outputImage;

        return $this;
    }

    /**
     * Sets background color hexadecimal code.
     *
     * @param string $backgroundColor
     *
     * @return GDTool
     */
    public function setBackgroundColor($backgroundColor)
    {
        $this->backgroundColor = $backgroundColor;

        return $this;
    }

    /**
     * Sets the font to be used.
     *
     * @param string $font
     *
     * @return GDTool
     */
    public function setFont($font)
    {
        $this->font = $font;

        return $this;
    }

    /**
     * Sets font color hexadecimal code.
     *
     * @param string $fontColor
     *
     * @return GDTool
     */
    public function setFontColor($fontColor)
    {
        $this->fontColor = $fontColor;

        return $this;
    }

    /**
     * Sets font size.
     *
     * @param int $fontSize
     *
     * @return GDTool
     */
    public function setFontSize($fontSize)
    {
        $this->fontSize = $fontSize;

        return $this;
    }

    /**
     * Sets height of each lines.
     *
     * @param int $lineHeight
     *
     * @return GDTool
     */
    public function setLineHeight($lineHeight)
    {
        $this->lineHeight = $lineHeight;

        return $this;
    }

    /**
     * Sets the angle of the line.
     *
     * @param int $angle
     *
     * @return GDTool
     */
    public function setAngle($angle)
    {
        $this->angle = $angle;

        return $this;
    }

    /**
     * Sets the horizontal position of the text.
     *
     * @param int $horizontalPosition
     *
     * @return GDTool
     */
    public function setHorizontalPosition($horizontalPosition)
    {
        $this->horizontalPosition = $horizontalPosition;

        return $this;
    }

    /**
     * Sets the vertical position of the text.
     *
     * @param int $verticalPosition
     *
     * @return GDTool
     */
    public function setVerticalPosition($verticalPosition)
    {
        $this->verticalPosition = $verticalPosition;

        return $this;
    }

    /**
     * Set whether using local (Persian) numeric character or not.
     *
     * @param bool $useLocalNumber
     *
     * @return GDTool
     */
    public function setUseLocalNumber($useLocalNumber)
    {
        $this->useLocalNumber = $useLocalNumber;

        return $this;
    }

    /**
     * Sets the decorator.
     *
     * @param StringDecorator $decorator
     *
     * @return GDTool
     */
    public function setDecorator(StringDecorator $decorator)
    {
        $this->decorator = $decorator;

        return $this;
    }

    /**
     * Sets class options.
     *
     * @param array $options
     *
     * @return GDTool
     */
    public function setOptions(array $options)
    {
        foreach ($options as $option => $value) {
            if (in_array($option, $this->getAvailableOptions())) {
                $this->$option = $value;
            }
        }

        return $this;
    }

    /**
     * Add new string line to image.
     *
     * @param string $line
     *
     * @return GDTool
     */
    public function addLine($line)
    {
        array_push($this->lines, $line);

        return $this;
    }

    /**
     * Add multiple line to the list of lines to be generated.
     *
     * @param array $lines
     *
     * @return GDTool
     */
    public function addLines(array $lines)
    {
        $lines = array_filter($lines, function ($line) {
            return is_string($line);
        });

        $this->lines = array_merge($this->lines, $lines);

        return $this;
    }

    /**
     * Generate requested image.
     *
     *
     * @return false|string
     */
    public function build()
    {
        $this->initDecorator();
        $this->initImage();
        $this->generateColorAllocates();
        $this->writeLines();

        return $this->generate();
    }

    /**
     * Get available options for class.
     *
     *
     * @return array
     */
    protected function getAvailableOptions()
    {
        return array_keys(get_object_vars($this));
    }

    /**
     * Initialize decorator.
     */
    protected function initDecorator()
    {
        if (!isset($this->decorator) || is_null($this->decorator)) {
            $this->decorator = new PersianStringDecorator();
        }
    }

    /**
     * Initialize image canvas.
     */
    protected function initImage()
    {
        $this->imageResource = imagecreate($this->width, $this->getHeight());
    }

    /**
     * Calculate and return the height of canvas.
     *
     *
     * @return int
     */
    protected function getHeight()
    {
        $lineCounts = count($this->lines);

        return ($lineCounts + 1) * $this->lineHeight;
    }

    protected function generateColorAllocates()
    {
        $this->generateBackgroundAllocate();
        $this->generateFontAllocate();
    }

    protected function generateBackgroundAllocate()
    {
        list($red, $green, $blue) = $this->getRGBValues($this->backgroundColor);
        $this->backgroundColorAllocate = imagecolorallocate(
            $this->imageResource,
            $red,
            $green,
            $blue
        );
    }

    protected function generateFontAllocate()
    {
        list($red, $green, $blue) = $this->getRGBValues($this->fontColor);
        $this->fontColorAllocate = imagecolorallocate(
            $this->imageResource,
            $red,
            $green,
            $blue
        );
    }

    /**
     * Get RGB value of a hexadecimal color code.
     *
     * @param string $hexColor
     *
     * @throws PersianGDException
     *
     * @return array
     */
    protected function getRGBValues($hexColor)
    {
        if (substr($hexColor, 0, 1) != '#') {
            throw new PersianGDException('Invalid hexadecimal color code provided');
        }

        $hexColor = substr($hexColor, 1);

        if (strlen($hexColor) == 3) {
            $red = str_repeat(substr($hexColor, 0, 1), 2);
            $green = str_repeat(substr($hexColor, 1, 1), 2);
            $blue = str_repeat(substr($hexColor, 2, 1), 2);
        } else {
            if (strlen($hexColor) == 6) {
                $red = substr($hexColor, 0, 2);
                $green = substr($hexColor, 2, 2);
                $blue = substr($hexColor, 4, 2);
            } else {
                throw new PersianGDException('Invalid hexadecimal color code provided');
            }
        }

        return [
            hexdec($red),
            hexdec($green),
            hexdec($blue),
        ];
    }

    protected function writeLines()
    {
        $verticalPos = $this->verticalPosition;
        foreach ($this->lines as $dir => $line) {
            imagettftext(
                $this->imageResource,
                $this->fontSize,
                $this->angle,
                $this->horizontalPosition,
                $verticalPos,
                $this->fontColorAllocate,
                $this->font,
                $this->decorator->decorate($line, $this->useLocalNumber)
            );

            $verticalPos += $this->lineHeight;
        }
    }

    protected function generate()
    {
        if ($this->outputImage) {
            ob_start();
            imagepng($this->imageResource);

            return ob_get_clean();
        }

        imagepng($this->imageResource, $this->fileName);

        return $this->fileName;
    }
}
