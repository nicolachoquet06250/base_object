<?php

namespace project\dao;
use project\extended\classes\dao;

class user_dao extends dao {
    protected $id;
    protected $nom;
    protected $prenom;
    protected $adresse;
    protected $adresse2;
    protected $code_postal;
    protected $ville;
    protected $date_naissence;
    protected $date_inscription;
}