<?php
namespace Bullhorn\FastRest\Api\Services
{
    class FileHandle 
    {
        /**
         * @type resource|false *
         */
        protected $handle;

        /**
         * Constructor
         *
         * @see fopen
         * @param string $filename
         * @param string $mode
         */
        public function __construct(string $filename, string $mode)
        {}

        /**
         * Destructor, closes the handle
         */
        public function __destruct()
        {}

        /**
         * Writes to the handle
         *
         * @param string $string
         * @return int|false returns the number of bytes written, or FALSE on error
         */
        public function write(string $stringg)
        {}

        /**
         * Reads from the handle
         *
         * @param int $length
         * @return string
         */
        public function read(int $length)
        {}

        /**
         * close
         *
         * @return void
         */
        public function close()
        {}

        /**
         * Getter
         *
         * @return resource
         */
        protected function getHandle()
        {}

        /**
         * Setter
         *
         * @param resource $handle
         */
        protected function setHandle($handle)
        {}

    }

}

