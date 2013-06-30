---
layout: default
slug  : installation
title : Installation
---

# Installation

The easiest way to install Oriancci is using composer, simply add Oriancci to your applications `composer.json` file.

    {
        "require": {
            "oriancci/oriancci": "dev-master"
        }
    }

If you'd like to use a logger to view SQL statements, try [monolog](https://github.com/Seldaek/monolog).

    {
        "require": {
            "oriancci/oriancci": "dev-master",
            "monolog/monolog": "1.6.*@dev"
        }
    }