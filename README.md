# Strelka Card Balance Notifier

A PHP script that automatically checks your Moscow transport card Strelka ("СТРЕЛКА") balance and sends email notifications.

*Motivation: Strelka doesn't send any notification after payment or charge. For the balance you need to visit official website. This script helps you to keep track of your balance and send an email if it changes.*

## Features
- Checks Strelka card balance via official API
- Sends email notifications with current balance
- Can be automated via cron job

## Requirements
- PHP 7.0 or higher
- Access to cron jobs
- SMTP server for sending emails

## Installation
1. Clone this repository
1. Configure your SMTP server in the script
1. Set up your web server, PHP engine, SMTP client and a cron job to run the script at your desired interval
1. Enjoy!
