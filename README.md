# Deploy by Vetermanve (powered by Janson)

### About
This is a simple self-written tool to simplify merge and deploy in today's reality.

### Requires
To get this awesome thing work you should have
- the last "git" version installed and
- "shot_open_tags" enabled on php.ini

### Get Started

#### INSTALL (you need docker and docker-compose)

1. Clone, install and start it
```shell
git clone git@github.com:janson-git/deploy.git
cd deploy
make install
make up
```

2. Open http://localhost:9088/ in your browser. Register in your  `deploy` app
3. At this step lets create personal ssh key for your `deploy` account. That key will use in interactions with github.com from `deploy`.
   I recommend DO NOT USE your personal SSH key for that, but create new one for `deploy`.
   a. create ssh keys with ssh-keygen command
   b. upload .pub key part to your github account
   c. put you private key part to your `deploy` app account on page  http://localhost:9088/web/user (click `Add SSH key` button)
4. NOTE: now it now showed if SSH key already uploaded to app


#### CREATE NEW PROJECT

Ok, app prepared, and we need add our first project to work with it
1. **variant 1 (UI):** Add new repository which needed to create your project: 
   - open UI on `Git` page (http://localhost:9088/web/deploy)
   - click `Add repository` button
   - Now just write URL or SSH link (HTTPS `https://github.com/janson-git/deploy.git` or SSH `git@github.com:janson-git/deploy.git`) of your repository to input field and press the `Save` button 
2. **OR variant 2 (terminal):** Go to `storage/repos` directory of your `deploy` app. This directory will contain local repositories of our projects. Clone repository that you want to use with `deploy`. Something like this:
   ```shell
   cd storage/repos
   git clone git@github.com:janson-git/deploy.git
   ```
3. If previous step finished successfully, we can create new project for this repo form UI.
4. Open in browser http://localhost:9088/web/project and click `Create new project`
5. Find your cloned project directory in directory navigator. Navigate by click on folder names.
6. When you found it (you will see branches list on that page), mark folder with checkbox and click on `Build Project`
7. Right after that click 'Save Project`


#### CREATE PACK IN PROJECT

Congratulations!
It is time to create our first release branch!
`deploy` works with PACKS and BUILDS.
PACK - it is just kind of plan: branches list that you want to merge in release branch.
BUILD - result of merge PACK branches to one branch. In fact `build` - it is release branch.

`.gitconfig` file in project root directory contains username and email which will used as owner info for pushed release branches.

Default content of .gitconfig is:
```shell
[user]
name = "Release Builder"
email = "deploy@local"
```

1. Ok, click on `Create new pack` button on your Project page
2. Now you need to set release branch name
3. For example, lets put `release-01` to pack name field
4. Now check all branches that you want to add to your pack, and click `Save pack` button
   Now app will fetch repository and create new local release branch with name like `build-release_01-20230331-214701`
   But it is still empty branch (equal to `master`) without pack branches right now
5. Click `Merge branches` button to start merging process. If CONFLICT happens - read `RESOLVING MERGE CONFLICTS` doc below
6. When all is ok, you can push release branch to repository. Just click on `Push build to repository` button
7. Check remote repository, and you will see new release branch!


#### RESOLVING MERGE CONFLICTS
You need resolve conflicts by creating merge-branches and add them to pack.

1. If you get error on merge and see `CONLICT` in logs then return to pack page and click `Search conflict branches` button
2. After short or long time you will see something like this:
```
task-xxx: ok
#1: TROUBLE: task-yyy TO master
MERGE_BRANCH: merge-0331-task-yyy-to-master
DESC: Auto-merging run.php
CONFLICT (content): Merge conflict in run.php
Automatic merge failed; fix conflicts and then commit the result.
DIFF: diff --cc run.php
...
...
```

What you can see here?

- conflict on merging `task-yyy` branch to `master` branch
- recommended name of merge-branch `merge-0331-task-yyy-to-master`
- DIFF is showed for details

Now you need to use portion of git-kung-fu:
1. create new merge-branch based on `master` (I recommend to use name from conflict descriptions: in this case you will see what branches is conflicted and merged for each merge-branch)
2. merge `task-yyy` branch to that new merge-branch, you need to resolve conflicts on this step
3. commit and push merge-branch to repository
4. return to Pack page, click `Add branches` button, find your merge-branch in list and accept it to pack
5. then on pack page click `Remove build` and after that - click `Merge branches` again
6. Now it must be ok. If not - ask me
7. After all is ok, you have good state release build which can be pushed to repository


### Security

Deploy has authorisation, but it mainly "identification".    
Registration are completely open, user password hashes are available through, inner database management tool.
Please secure access to deploy by your way or send me pull request to improve situation. 
