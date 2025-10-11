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

To edit roles and create new ones, go to the Roles tab and do it. Also you can create own player's commands. Check `sv_gsc-panel_commands.lua` for examples. To these commands appear at web-app press `Update rigths` at Roles tab. Also exists little issue that you need to relogin at your account to get new rights

To verify your Gmod server, you need to generate a token in the Gmod Token tab and insert it at the beginning of the `sv_gsc-panel.lua` file, replacing `YOUR TOKEN`.

Working directly with players, changing maps, gamemodes, etc., is generally elementary and requires no explanation.

## Installation
0. Dowload `Docker`
1. Dowload docker image by typing this command: `docker pull ghcr.io/boblikut/gsc-panel:latest`
2. Transfer folder from `addons` to your `addons` folder on your gmod server
3. Open `sv_gsc-panel.lua` and replace `YOUR TOKEN` on your token and change `ws://localhost:8080` on actual websocket adress(`ws://[DOMEN]:[PORT](8080 by default if you don't change the launch command)`)

## Launch
1. Create and launch docker container thx this command: `docker run --name gsc-panel -d -p 8080:8080 -p 80:80 -v gsc-db-volume:/var/www/html/db ghcr.io/boblikut/gsc-panel:latest`
2. To restart container type: `docker restart gsc-panel`

The addon includes systems that make it independent of the launch order, so the order is not important.

If you installed the addon correctly and the server is running, you will immediately see up-to-date statistics, players, etc.

## Screenshots

<img width="1918" height="897" alt="image" src="https://github.com/user-attachments/assets/b59c72a0-2717-4c30-b9bb-29dcebd8f1dd" />

<img width="1886" height="896" alt="image" src="https://github.com/user-attachments/assets/19c949a7-dedb-4141-b05c-a0a73b19f433" />

<img width="1892" height="885" alt="image" src="https://github.com/user-attachments/assets/865f3f3c-317b-467a-887c-bed00ca274cc" />

<img width="1883" height="887" alt="image" src="https://github.com/user-attachments/assets/09d7e601-b5c6-417e-816b-218f5063a488" />


## Afterword
If you have any problems with the project, suggestions, wishes, etc., then write to me on Discord (`boblikut`). I can also create custom command packs specifically for your server, but if you really like the project, I would first use it in its vanilla version to understand if you really need it and if there are any critical bugs in the project.

Just so you know, I'm all for using the project and you can use it absolutely freely and without mentioning authorship (but also without claiming it as your own). Also, if you use this system on your project, you can write to me on Discord, and I will add your server to the `Servers Already Using GSC-Panel` tab.

## Acknowledgments

* [TrafeX](https://github.com/TrafeX) - cool [docker image](https://github.com/TrafeX/docker-php-nginx) for multiplatform PHP apps
* [Unknown Develiper](https://github.com/unknown-gd) - help with Docker
* [Winkarst](https://github.com/Winkarst-cpu) - testing

## Servers Already Using GSC-Panel
* No one yet) Be the first!
