# Deploy by Vetermanve
### About
This is a simple self-written tool to simplify merge and deploy in today's reality.
### Requires
To get this awesome thing work you should have
 - the last "git" version installed and
 - "shot_open_tags" enabled on php.ini

### Get Started
To start working you should create directory "bulder"(or else) and place deploy and you repos into it.

eg
```
/var/www/builder/deploy
/var/www/builder/your_project1
/var/www/builder/your_project2
```
you can place projects even in subdirectories
```
/var/www/builder/deploy
/var/www/builder/some_group/your_project1
/var/www/builder/some_group/your_project2
```

### Security

Deploy has authorisation, but it mainly "identification".    
Registration are completely open, user password hashes are available through, inner database management tool.
Please secure access to deploy by your way or send me pull request to improve situation. 

### PS
I'll be pleased to help you, send me pull requests and money ;)

This tool was not fully-futuristic designed and contains some ugly and unused code written years ago.

But it works =)
