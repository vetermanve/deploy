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

### Configuration via builder.yml

Put builder.yml to your project for enable minor-versioning via git tag:
```
production-major:
	name: 'deploy.ka (increment major version)'
	tag: /^prod\_release.*$/
	type: tag
	release: major
	confirm: true
	callback: http://deploy.ka/release?key=secret_key&message=
	
production-patch:
	name: 'deploy.ka (increment patch version)'
	tag: /^prod\_release.*$/
	type: tag
	release: patch
	slack: https://hooks.slack.com/services/your/secret/key
	
int:
	name: 'int.deploy.ka'
	tag: /^int\_release.*$/
	type: tag

```

- type | string - Deploy type ("tag")
- confirm | boolean - Ask before start?
- name | string - Visible name on button
- tag | string - Regex for tag search
- release | string - "major" (*1.0.0->2.0.0), "minor" (*1.0.0->1.1.0) or "patch" (*1.0.0->1.0.1) version increment-style
- slack | string - Url for send messages to slack
- callback | string - Url for callback, message placed to %text% param, example "http://deploy.ka/release?text=%text%"

### Security

Deploy has authorisation, but it mainly "identification".    
Registration are completely open, user password hashes are available through, inner database management tool.
Please secure access to deploy by your way or send me pull request to improve situation. 

### PS
I'll be pleased to help you, send me pull requests and money ;)

This tool was not fully-futuristic designed and contains some ugly and unused code written years ago.

But it works =)
