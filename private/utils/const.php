<?php

const STATUS_NOTHING = 0;
const STATUS_EMAIL_VERIFIED = 1;
const STATUS_EMAIL_NEWS_LETTER = 1 << 1;

const FORUM_PERMISSION_VIEW_MASK = 0b1111;
const FORUM_PERMISSION_CREATE_MASK = 0b1111 << 4;

const FORUM_PERMISSION_VIEW_EVERYONE = 0b0000;
const FORUM_PERMISSION_VIEW_CONNECTED = 0b0001;
const FORUM_PERMISSION_VIEW_MODERATOR = 0b0010;
const FORUM_PERMISSION_VIEW_ADMIN = 0b0011;

const FORUM_PERMISSION_CREATE_EVERYONE = 0b0000 << 4;
const FORUM_PERMISSION_CREATE_CONNECTED = 0b0001 << 4;
const FORUM_PERMISSION_CREATE_MODERATOR = 0b0010 << 4;
const FORUM_PERMISSION_CREATE_ADMIN = 0b0011 << 4;

const PERMISSION_ADMIN = 255;
const PERMISSION_MODERATOR = 200;
const PERMISSION_DEFAULT = 0;
const PERMISSION_DISCONNECT = -1;

const PROJECT_STATUS_WAIT_VERIFICATION = 0;
const PROJECT_STATUS_REJECT = 1;
const PROJECT_STATUS_ACCEPTED_NO_CONTENT = 2;
const PROJECT_STATUS_PUBLISHED = 3;

const PICTURE_FORMAT_NONE = 'n';
const PICTURE_FORMAT_WEBP = 'w';
const PICTURE_FORMAT_PNG = 'p';
const PICTURE_FORMAT_JPG = 'j';
const PICTURE_FORMAT_GIF = 'g';

const FORMAT_EXTENSION = [
    PICTURE_FORMAT_NONE => null,
    PICTURE_FORMAT_WEBP => 'wepb',
    PICTURE_FORMAT_PNG => 'png',
    PICTURE_FORMAT_JPG => 'jpg',
    PICTURE_FORMAT_GIF => 'gif',
];