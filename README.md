# WebOffice
WebOffice is an all-in-one adminstrative/network/server tool including high security a controls and manages all of your connected devices. It also contains office suite software which can manage all of your work without problems.

***

## Supported Devices
> Due to having command lines, it only works on desktops.
* Windows
* MAC 
* Linux

***

## Required Libraries

**Python (13.3+)**
* [PIP](https://github.com/pypa/pip)
* [PSUtil](https://github.com/giampaolo/psutil)
* [GPUtil](https://github.com/anderskm/gputil)
* [setuptools](https://github.com/pypa/setuptools)

**PHP (8.2+)**
* PDO
* mySQL

**Software**
* OpenSSH
* [speedtest-cli](https://github.com/sivel/speedtest-cli) 
***
## Features
* Hardware graphs
* AD(DS) software
* Addons/Themes
* Office Suite (Docs, Mail, Powerpoint, Spreadsheets, etc.)
* HR Tools
* Network/Server tools
* And more.

***

## Security and Privacy
* WebOffice limits the usage of 3rd party scrips/stylesheets
Here is the 3rd parties that are being used
- [Bootstrap (v5.3)](https://getbootstrap.com/) - **Usage:** Styleing and Scripting
- [DataTables (v2.3.4)](https://datatables.net/) - **Usage:** UI tables
- [Leaflet (v2.0.0-alpha.1)](https://leafletjs.com/) - **Usage:** GPS/Location GUI
- [Plotly (v3.1.0)](https://plotly.com/) - **Usage:** Graph/Chart visualization

_The software does use:_
- GEOLocation API
- Navigator API
- Wifi/Internet connection
- Devices hardware/components
- Terminal/Command scripts
- Network connections
- Browser history

**Note:** Also this may require you to give _daemon_ priviages if you are on Linux
```bash
sudo visudo
```
inside of _sudoers.tmp_
```bash
# Local server permissions
daemon ALL=(ALL) NOPASSWD: /usr/sbin/dmidecode
daemon ALL=(ALL) NOPASSWD: /sbin/shutdown
daemon ALL=(ALL) NOPASSWD: /sbin/reboot
```

