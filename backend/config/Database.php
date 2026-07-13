<?php

class Database
{
    private ?PDO $connection = null; //? jelentése: ez vagy PDO vagy NUll. 
    // Lusta kapcsolat: eleinte nem létezik, csak akkor jön létre amikor először kérjük, nincsenek felesleges drága műveletek

    private string $host;
    private string $database;
    private string $username;
    private string $password;
    private string $charset;

    public function __construct()
    {
        //:: jelentése: Találd meg ebben a változóban/fájlban ezt "valami", ha nincs meg használd ezt: "fallback érték"
        $this->host = Env::get('DB_HOST', 'localhost'); 
        $this->database = Env::get('DB_NAME', 'otthon_plusz');
        $this->username = Env::get('DB_USER', 'root');
        $this->password = Env::get('DB_PASS', '');
        $this->charset = Env::get('DB_CHARSET', 'utf8');
    }

    public function getConnection(): PDO
    {
        if ($this->connection !== null) {
            return $this->connection; //kódismétlés? ugyanez van a végén is. miért?
        }

        //dsn: data source name
/*
mysql:host=localhost;dbname=otthon_plusz;charset=utf8
└─┬─┘ └────┬────┘ └──────┬──────┘ └────┬────┘
 driver     hol           melyik AB      kódolás
*/
        $dsn = "mysql:host={$this->host};dbname={$this->database};charset={$this->charset}";

        $options = [ //ezeket magyarázd el
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, //error handling
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, //kiolvasott adatbázis sor asszociatív tömbként jöjjön vissza, ahol a kulcs az oszlopnév. Sor elérése: $sor['megnevezes'] 
            PDO::ATTR_EMULATE_PREPARES => false, //valódi prepare és nem megjátszás
        ];

        //Biztonsági okokból külön van a felh és a jelszó az előbbi dsn értékadástól.
/*
new PDO($dsn, $this->username, $this->password, $options);
└─┬─┘  └──────┬──────┘  └──────┬──────┘  └──┬──┘
DSN      felh.név          jelszó       beállítások
*/
        $this->connection = new PDO($dsn, $this->username, $this->password, $options);

        return $this->connection; 
    }
}