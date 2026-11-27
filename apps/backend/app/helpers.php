<?php

const AUTH_TOKEN_NAME = 'api';
const AUTH_TOKEN_TYPE = 'Bearer';
const AUTH_TOKEN_ABILITIES = ['*'];

const PERMISSION_GUARD = 'web';

const LOGIN_FIELD = 'login';
const LOGIN_DUMMY_HASH_CACHE_KEY = 'auth.login.dummy_hash';
const FAILED_LOGIN_ATTEMPTS_MAX = 255;
const LOGIN_IDENTIFIER_MAX_LENGTH = 120;
const LOGIN_PASSWORD_MAX_LENGTH = 255;
const USER_AGENT_MAX_LENGTH = 255;

const USERNAME_PATTERN = '/^(?=.*[A-Z])(?=.*\d)[A-Za-z0-9]{8,20}$/';
const PASSWORD_MIN_LENGTH = 8;
const IDENTIFICATION_PATTERN = '/^\d{10}$/';
const IDENTIFICATION_REPEATED_DIGITS_PATTERN = '/(\d)\1{3}/';
const PERSON_NAME_PATTERN = "/^(?=.*\pL)[\pL\s'-]+$/u";
const PERSON_NAME_MAX_LENGTH = 60;

const EMAIL_LOCAL_PART_MAX_LENGTH = 100;
const EMAIL_FALLBACK_LOCAL_PART = 'usuario';
const EMAIL_GENERATION_ATTEMPTS = 10;
const EMAIL_UNIQUE_INDEX = 'users_email_unique';

const USERS_PER_PAGE_DEFAULT = 15;
const USERS_PER_PAGE_MAX = 100;
const USER_SEARCH_MIN_LENGTH = 3;
const USER_SEARCH_MAX_LENGTH = 100;

const IMPORT_COLUMNS = ['first_name', 'middle_name', 'first_surname', 'second_surname', 'identification', 'birth_date', 'username'];
const IMPORT_MAX_FILE_KB = 10240;
const IMPORT_CHUNK_SIZE = 500;
const IMPORT_QUEUE = 'imports';
const IMPORT_JOB_TIMEOUT = 900;
const IMPORT_STORAGE_DIRECTORY = 'imports';
const IMPORT_UNUSABLE_PASSWORD_LENGTH = 40;
const IMPORT_ROW_IDENTIFICATION_MAX_LENGTH = 50;
const IMPORT_EXTENSIONS = ['csv', 'xlsx'];
const IMPORT_FILE_NAME_MAX_LENGTH = 255;
const IMPORT_ROWS_PER_PAGE_DEFAULT = 50;

const MENU_ROOT_ID = 0;

const DB_TRANSACTION_ATTEMPTS = 3;

const API_DOCS_UI_PATH = 'docs/api';
const API_DOCS_JSON_PATH = 'docs/api.json';

const SAMPLE_USERS_DEFAULT = 50;
const SAMPLE_USERNAME_PREFIX = 'Muestra';
const SAMPLE_USER_PASSWORD = 'Muestra!2026';
const SAMPLE_STATUS_CYCLE = 10;
const SAMPLE_BLOCKED_POSITION = 0;
const SAMPLE_INACTIVE_POSITION = 5;
