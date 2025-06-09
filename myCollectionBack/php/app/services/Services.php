<?php

namespace MyCollection\app\services;

class Services
{
    private static Services $instance;

    private ObjetServices $objetServices;
    private ProprietaireService $proprietaireService;

    private CategorieServices $categorieServices;

    private MediaServices $mediaServices;

    private function __construct()
    {
        $this->objetServices = new ObjetServices();
        $this->proprietaireService = new ProprietaireService();
        $this->mediaServices = new MediaServices();
        $this->categorieServices = new CategorieServices();


    }

    public static function instance(): Services
    {
        if (!isset(self::$instance)) {
            self::$instance = new Services();
        }
        return self::$instance;
    }

    public function getObjetServices(): ObjetServices
    {
        return $this->objetServices;
    }

    public function getProprietaireService(): ProprietaireService
    {
        return $this->proprietaireService;
    }

    public function getMediaServices(): MediaServices
    {
        return $this->mediaServices;
    }

    public function getCategorieServices(): CategorieServices
    {
        return $this->categorieServices;
    }




}