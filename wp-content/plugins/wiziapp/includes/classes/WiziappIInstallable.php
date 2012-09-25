<?php

interface WiziappIInstallable
{
    function isInstalled();
    function install();
    function uninstall();
    function needUpgrade();
    function upgrade();
}