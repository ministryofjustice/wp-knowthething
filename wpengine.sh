#!/usr/bin/env bash

environment=$1
theme="weekly"

###
# Determine which local branch to deploy from.
# If pushing to production environment, use master.
# If pushing to staging, look for a 2nd script parameter and fallback to develop.
###
if [ "$environment" == "staging" ]
then
  if [ ! -z "$2" ]
  then
    branch=$2
  else
    branch="develop"
  fi
elif [ "$environment" == "production" ]
then
  branch="master"
else
  echo "Invalid environment supplied: $environment"
  echo "Please invoke this script with a valid environment."
  echo "Valid environments are: staging, production"
  exit
fi

###
# Check that we have the correct working directory.
###
if [ ! -d "web" ]
then
  echo "Please run this script from the main htdocs directory."
  exit
fi

###
# Stop if there are uncommitted changes
###
if [[ -n $(git status -s) ]]
then
  echo "Please review and commit your changes before continuing."
  exit
fi

###
# Create "deploy" directory
# If necessary, remove existing directory
###
cd ".."
if [ -d "deploy" ]
then
  echo "Removing old deployment directory."
  rm -Rf "deploy"
fi

echo "Preparing files for deployment."
cp -a "htdocs" "deploy"
cd "deploy"

###
# Build theme assets with gulp
###
echo "Building theme assets."
cd "web/app/themes/${theme}"
git checkout "$branch"
npm install
bower install
gulp --production
cd "../../../.."

###
# Create a temporary wpengine branch
###
exists=`git show-ref refs/heads/wpengine`
if [ -n "$exists" ]
then
  git branch -D wpengine
fi
git checkout -b wpengine

###
# Move files into the expected locations.
# Remove unwanted files.
###
mv web/app wp-content
rm -R web
rm "wp-content/themes/${theme}/.gitignore"
rm "wp-content/mu-plugins/bedrock-autoloader.php"
rm "wp-content/mu-plugins/disallow-indexing.php"
rm "wp-content/mu-plugins/register-theme-directory.php"
rm .gitignore
rm "vendor/composer/autoload_static.php" # Presence of this file breaks wpengine syntax checks, so we must remove it. See https://github.com/composer/composer/issues/5316
echo '/*' >> .gitignore
echo '!wp-content/' >> .gitignore
echo 'wp-content/uploads' >> .gitignore
git ls-files | xargs git rm --cached

cd wp-content/
find . | grep .git | xargs rm -rf
cd ../

###
# Commit new structure into git, and push to remote.
###
git add .
git commit -am "WP Engine build from: $(git log -1 HEAD --pretty=format:%s)$(git rev-parse --short HEAD 2> /dev/null | sed "s/\(.*\)/@\1/")"

echo "Pushing to WP Engine..."
if [ "$environment" == "staging" ]
then
  git push staging wpengine:master --force
elif [ "$environment" == "production" ]
then
  git push production wpengine:master --force
fi
git checkout "$branch"
git branch -D wpengine
echo "Successfully deployed."

###
# Remove deploy directory and move back to htdocs
###
echo "Cleaning up..."
cd "../htdocs"
rm -Rf "../deploy"
git fetch
echo "Done."