#!/usr/bin/env bash

environment=$1
theme="weekly"

echo "Starting the build process..."
cd "web/app/themes/${theme}"
if [ "$environment" == "staging" ]
then
  git checkout develop
  gulp
elif [ "$environment" == "production" ]
then
  git checkout master
  gulp --production
else
  echo "Invalid environment."
  exit
fi
cd "../../../.."

if [[ -n $(git status -s) ]]
then
  echo "Please review and commit your changes before continuing..."
  exit
fi

exists=`git show-ref refs/heads/wpengine`
if [ -n "$exists" ]
then
  git branch -D wpengine
fi
git checkout -b wpengine

mv web/app wp-content
rm -R web
rm "wp-content/themes/${theme}/.gitignore"
rm "wp-content/mu-plugins/bedrock-autoloader.php"
rm "wp-content/mu-plugins/disallow-indexing.php"
rm "wp-content/mu-plugins/register-theme-directory.php"
rm .gitignore
echo "/*\n!wp-content/\nwp-content/uploads" >> .gitignore
git ls-files | xargs git rm --cached

cd wp-content/
find . | grep .git | xargs rm -rf
cd ../

git add .
git commit -am "WPEngine build from: $(git log -1 HEAD --pretty=format:%s)$(git rev-parse --short HEAD 2> /dev/null | sed "s/\(.*\)/@\1/")"

echo "Pushing to WPEngine..."
if [ "$environment" == "staging" ]
then
  git push staging wpengine:master --force
  git checkout develop
elif [ "$environment" == "production" ]
then
  git push production wpengine:master --force
  git checkout master
fi
git branch -D wpengine
rm -rf wp-content/
echo "Successfully deployed."
