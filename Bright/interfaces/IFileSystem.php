<?php


namespace Bright\interfaces;


interface IFileSystem
{
    /**
     * Gets additional information about a file;
     * @param string $file The path to the file
     * @since 2.3
     * @return object An object containing the file size, and, if it's an image, the dimensions of the image
     */
    public function getProperties($file);

    /**
     * Gets the subfolders of a given directory<br/>
     * Required permissions:<br/>
     * <ul>
     * <li>IS_AUTH</li>
     * </ul>
     * @param string $dir The parent directory, relative to the filepath specified in config.ini
     * @return array An array of directories (OFolders)
     * @throws \Exception
     */
    public function getSubFolders($dir = '');

    /**
     * Gets the entire folder structure of the user upload directory<br/>
     * Required permissions:<br/>
     * <ul>
     * <li>IS_AUTH</li>
     * </ul>
     * @return array A multi-dimensional array of OFolders
     * @throws \Exception
     */
    public function getStructure();

    /**
     * Gets the files in the given folder
     * @param string $dir The foldername
     * @param bool $returnThumbs
     * @param null $exclude_ext
     * @param bool $extended
     * @param null $include_ext
     * @return array An array of files
     * @throws \Exception
     */
    public function getFiles($dir = '', $returnThumbs = true, $exclude_ext = null, $extended = false, $include_ext = null);

    /**
     * Creates a folder<br/>
     * Required permissions:<br/>
     * <ul>
     * <li>IS_AUTH</li>
     * </ul>
     * @param string $folderName The name of the folder to create
     * @param string $dir The path of the parentfolder, relative to the base folder specified in the config.ini
     * @return array An array of folders, which are the subfolders of $dir
     * @throws \Exception
     */
    public function createFolder($folderName, $dir);

    /**
     * Deletes a directory<br/>
     * Required permissions:<br/>
     * <ul>
     * <li>IS_AUTH</li>
     * </ul>
     * @param string $folderName The name of directory to delete
     * @param string $parent The directory in which the dir to delete is located, relative to the base folder specified in the config.ini
     * @return array The sub-dirs of $parent
     * @throws \Exception
     */
    public function deleteFolder($folderName, $parent);


    /**
     * Moves a file from oldpath to newpath<br/>
     * Required permissions:<br/>
     * <ul>
     * <li>IS_AUTH</li>
     * </ul>
     * @param string $oldPath The directory where the file currently resides, relative to the base folder specified in the config.ini
     * @param string $newPath The target directory, relative to the base folder specified in the config.ini
     * @param string $filename The file to move
     * @return array The contents of oldpath
     * @throws \Exception
     */
    public function moveFile($oldPath, $newPath, $filename);

    /**
     * Deletes a file<br/>
     * Required permissions:<br/>
     * <ul>
     * <li>IS_AUTH</li>
     * <li>DELETE_FILE</li>
     * </ul>
     * @param string $filename The file to delete
     * @param string $dir The path of the file, relative to the base folder specified in the config.ini
     * @param boolean $throwNotExistsException When true, an exception is thrown when the specified file does not exist
     * @return array An array of files, which are in $path
     * @throws \Exception
     */
    public function deleteFile($filename, $dir, $throwNotExistsException = false);

    public function deleteFiles($files, $path);

    /**
     * Downloads a file from the given url and stores it on the server
     * @param string $url The url of the file
     * @param string $filename The filename on the local server
     * @param string $parent The parent folder
     * @return object
     * @throws \Exception
     */
    public function uploadFromUrl($url, $filename, $parent);



}