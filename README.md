# GSC - Panel (Beta)
## Intro
This is an administration panel for Garry's Mod servers. Your administrators will be able to perform their duties, even without the ability to log into the Garry's Mod server, via a convenient web interface.

## About the Project
The project is a self-hosted web application + scripts for Garry's Mod. This means you will have to host this project on your own hosting or by other means.

## Usage
First of all, you need to log into the admin account somehow. It's simple to do. By default, the following account is already created in the database:

Login: `owner`
Password: `1234`

Immediately after logging into the account, change your password. To do this, go to the Admin Panel, users, and click Edit next to the owner nickname. You can also change the login there.

To create accounts for your admins, you can click on Create Account in the users tab.

To edit roles and create new ones, go to the Roles tab and do it.

To verify your Gmod server, you need to generate a token in the Gmod Token tab and insert it at the beginning of the `sv_gsc-panel.lua` file, replacing `YOUR TOKEN`.

Working directly with players, changing maps, gamemodes, etc., is generally elementary and requires no explanation.

## Installation
A rather complex step for beginners, but if you have basic server administration skills, it won't be difficult. I won't provide specific commands, as they may differ depending on the Linux distribution and corresponding package managers. The instructions described here should generally be common to all OSes.

0. Dowload Docker
1. Dowload docker image by typing this command: `docker pull ghcr.io/boblikut/gsc-panel:latest`

## Launch
1. Create and launch docker container thx this command: `docker run --name gsc-panel -d -p 8080:8080 -p 80:80 -v gsc-db-volume:/var/www/html/db ghcr.io/boblikut/gsc-panel:latest`
2. To restart container type: `docker restart gsc-panel`

The addon includes systems that make it independent of the launch order, so the order is not important.

If you installed the addon correctly and the server is running, you will immediately see up-to-date statistics, players, etc.

## Screenshots

<img width="1917" height="885" alt="image" src="https://github.com/user-attachments/assets/bd035534-b5eb-40ac-af45-e26f4f4c6d6c" />

<img width="1887" height="782" alt="image" src="https://github.com/user-attachments/assets/02db8c15-9e9c-40d6-b04d-7ca7a14ac59a" />

<img width="1892" height="886" alt="image" src="https://github.com/user-attachments/assets/f8661812-3ed6-4e51-a846-eef425276ce7" />

<img width="1886" height="877" alt="image" src="https://github.com/user-attachments/assets/d5c1ad91-3500-4ca8-b3bf-ae138fd8f430" />

<img width="1887" height="858" alt="image" src="https://github.com/user-attachments/assets/193493ef-2dd1-4adc-98f8-9978a2750415" />

## Afterword
If you have any problems with the project, suggestions, wishes, etc., then write to me on Discord (`boblikut`). I can also create custom command packs specifically for your server, but if you really like the project, I would first use it in its vanilla version to understand if you really need it and if there are any critical bugs in the project.

Just so you know, I'm all for using the project and you can use it absolutely freely and without mentioning authorship (but also without claiming it as your own). Also, if you use this system on your project, you can write to me on Discord, and I will add your server to the `Servers Already Using GSC-Panel` tab.

## Acknowledgments

* [TrafeX](https://github.com/TrafeX) - cool [docker image](https://github.com/TrafeX/docker-php-nginx) for multiplatform PHP apps
* [Unknown Develiper](https://github.com/unknown-gd) - help with Docker
* [Winkarst](https://github.com/Winkarst-cpu) - testing

## Servers Already Using GSC-Panel
* No one yet) Be the first!
