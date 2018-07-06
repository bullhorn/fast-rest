namespace Bullhorn\FastRest\Api\Services;

class FileHandle
{
    /** @type resource|false */
    protected handle;
    /**
     * Constructor
     * @see fopen
     * @param string $filename
     * @param string $mode
     */
    public function __construct(string filename, string mode) -> void
    {
        this->setHandle(fopen(filename, mode));
    }
    
    /**
     * Destructor, closes the handle
     */
    public function __destruct() -> void
    {
        this->close();
    }
    
    /**
     * Writes to the handle
     * @param string $string
     * @return int|false returns the number of bytes written, or FALSE on error
     */
    public function write(string stringg)
    {
        return fwrite(this->getHandle(), stringg);
    }
    
    /**
     * Reads from the handle
     * @param int $length
     * @return string
     */
    public function read(int length) -> string
    {
        return fread(this->getHandle(), length);
    }
    
    /**
     * close
     * @return void
     */
    public function close()
    {
        if this->getHandle() !== false {
            fclose(this->getHandle());
        }
    }
    
    /**
     * Getter
     * @return resource
     */
    protected function getHandle()
    {
        return this->handle;
    }
    
    /**
     * Setter
     * @param resource $handle
     */
    protected function setHandle(handle) -> void
    {
        let this->handle = handle;
    }

}