<?php
namespace Scm\PluginBid\Models\Enums;


use Illuminate\Http\UploadedFile;
use Intervention\Image\Facades\Image;
use ZipArchive;

enum TypeOfAcceptedFile : string {


    case UNKNOWN = 'unknown';
    case IMAGE = 'image';
    case COMPRESSED = 'compressed';

    case PDF =  'pdf';
    case SPREADSHEET = 'spreadsheet';

    case DOCUMENT = 'document';
    case PRESENTATION = 'presentation';

    /**
     * @throws \Exception
     */
    public static function calculateFileCategory(UploadedFile $file) : TypeOfAcceptedFile {
        if ($file->getSize() === 0) { return self::UNKNOWN;}

        //first test to see if valid image
        if (TypeOfAcceptedFile::isImageMime($file)) {
            if (TypeOfAcceptedFile::testForImage($file)) {
                return TypeOfAcceptedFile::IMAGE;
            }
        }

        //test if presentation
        if (TypeOfAcceptedFile::isPresentationMime($file)) {
            //need better way to test this, until now pass the slideshows
            return self::PRESENTATION;
        }


        //test if spreadsheet
        if (TypeOfAcceptedFile::isSpreadsheetMime($file)) {
            if (TypeOfAcceptedFile::testForSpreadsheet($file)) {
                return self::SPREADSHEET;
            }
        }

        //test if doc
        if (TypeOfAcceptedFile::isDocMime($file)) {
            if (TypeOfAcceptedFile::testForDoc($file)) {
                return self::DOCUMENT;
            }
        }


        //test if compressed, test after the office stuff, because they are compressed and will pass the archive test below!
        if (TypeOfAcceptedFile::isZipMime($file)) {
            if (TypeOfAcceptedFile::testForZip($file)) {
                return self::COMPRESSED;
            }
        }

        //test if pdf
        if (TypeOfAcceptedFile::isPdfMime($file)) {
            if (TypeOfAcceptedFile::testForPdf($file)) {
                return self::PDF;
            }
        }


        return TypeOfAcceptedFile::UNKNOWN;
    }



    public static function testForImage(UploadedFile $file) : bool {
        try {
            Image::make($file->path());
            return true;
        } catch (\Intervention\Image\Exception\NotReadableException) {
            //not an image, otherwise let the exception be thrown
        }
        return false;
    }

    public static function testForSpreadsheet(UploadedFile $file) : bool {
        try {
            \PhpOffice\PhpSpreadsheet\IOFactory::load($file->path());
            return true;
        } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception) {
            //not a spreadsheet
        }
        return false;
    }

    /**
     * @throws \Exception
     */
    public static function testForDoc(UploadedFile $file) : bool {
        try {
            \PhpOffice\PhpWord\IOFactory::load($file->path());
            return true;
        } catch (\Exception $e) {
            if (!str_contains($e->getMessage(),'is not a valid')) {
                throw $e; //some other issue
            }
        }
        return false;
    }

    public static function testForZip(UploadedFile $file) : bool {
        $zip = new ZipArchive;
        $res = $zip->open($file->path(), ZipArchive::RDONLY);
        if ($res === true) {
            $zip->close();
            return true;
        }
        return false;
    }

    /**
     * @throws \Exception
     */
    public static function testForPdf(UploadedFile $file) : bool {
        try {
            $parser = new \Smalot\PdfParser\Parser();
            $parser->parseFile($file->path());
            return true;
        } catch (\Exception $e) {
            if (!str_contains($e->getMessage(),'Invalid PDF')) {
                throw $e; //some other issue
            }
        }
        return false;
    }

    public static function isImageMime(UploadedFile $file) :bool {
        return str_contains($file->getMimeType(),'image');
    }

    public static function isZipMime(UploadedFile $file) :bool {
        return stripos($file->getMimeType(),'zip') !== false;
    }

    public static function isSpreadsheetMime(UploadedFile $file) :bool {
        return (stripos($file->getMimeType(),'spreadsheet') !== false)
            ||
            (stripos($file->getMimeType(),'ms-excel') !== false)
            ;
    }

    public static function isDocMime(UploadedFile $file) :bool {
        return ( stripos($file->getMimeType(),'msword') !== false)
            ||
            ( stripos($file->getMimeType(),'opendocument.text') !== false)
            ||
            ( stripos($file->getMimeType(),'wordprocessing') !== false)
            ||
            ( stripos($file->getMimeType(),'wpd') !== false)
            ;
    }

    public static function isPresentationMime(UploadedFile $file) :bool {
        return ( stripos($file->getMimeType(),'ms-powerpoint') !== false)
            ||
            ( stripos($file->getMimeType(),'presentation') !== false)
            ;
    }

    public static function isPdfMime(UploadedFile $file) :bool {
        return stripos($file->getMimeType(),'pdf') !== false;
    }
}


