<?php
use \Intervention\Image\ImageManagerStatic as ImageManager;

class Image
{

    public function createImage($file, $path, $mode, $settings)
    {

        $destination = BASEPATH . 'images' . DIRECTORY_SEPARATOR . $mode . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR;

        if (file_exists($destination . $file)) {
            // Do not regenerate
            return;
        }

        if (!is_dir($destination)) {
            mkdir($destination, 0775, true);
        }

        $settings['w'] = $settings['w'] ?? null;
        $settings['h'] = $settings['h'] ?? null;

        $img = ImageManager::make(BASEPATH . $path . DIRECTORY_SEPARATOR . $file);

        if (array_key_exists('zc', $settings)) {
            $img->fit($settings['w'], $settings['h']);
        } else {
            $img->resize(
                $settings['w'],
                $settings['h'],
                function ($constraint) use ($settings) {
                    $constraint->aspectRatio();
                    if (!array_key_exists('aoe', $settings)) {
                        $constraint->upsize();
                    }
                });

            if (array_key_exists('far', $settings)) {
                $img->resizeCanvas($settings['w'], $settings['h'], 'center', false, $settings['bg']);
            }
        }

        if(array_key_exists('fltr', $settings)) {
            foreach ($settings['fltr'] as $filter) {
                $filterArr = explode('|', $filter);

                switch ($filterArr[0]) {
                    case 'gray':
                        $img->greyscale();
                        break;
                    case 'gam':
                        $img->gamma((int)$filterArr[1]);
                        break;
                    case 'bord':
                        $img->rectangle(
                            0,
                            0,
                            $img->getWidth(),
                            $img->getHeight(),
                            function($draw) use ($filterArr) {
                                $draw->border($filterArr[1], $filterArr[4]);
                            }
                        );
                        break;
                }
            }
        }

        $quality = $settings['q'] ?? 90;

        $img->save($destination . $file, $quality);

        return $destination . $file;
    }
}

