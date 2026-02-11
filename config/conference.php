<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Conference File Storage Disk
    |--------------------------------------------------------------------------
    |
    | The disk to use for storing conference chat file uploads.
    |
    */
    'file_disk' => env('CONFERENCE_FILE_DISK', 's3'),

    /*
    |--------------------------------------------------------------------------
    | Conference Recording Storage Disk
    |--------------------------------------------------------------------------
    |
    | The disk to use for storing conference recordings and transcripts.
    |
    */
    'recording_disk' => env('CONFERENCE_RECORDING_DISK', 's3'),

];
