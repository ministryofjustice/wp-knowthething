environment=$1
theme="sage"


echo "Starting the build process..."
if [ "$environment" == "staging" ]
then
  git checkout develop &> /dev/null
  gulp &> /dev/null
elif [ "$environment" == "production" ]
then
  git checkout master &> /dev/null
  gulp --production &> /dev/null
else
  echo "Invalid environment."
  exit
fi

if [[ -n $(git status -s) ]]
then
  echo "Please review and commit your changes before continuing..."
  exit
fi

exists=`git show-ref refs/heads/wpengine`
if [ -n "$exists" ]
then
  git branch -D wpengine &> /dev/null
fi
git checkout -b wpengine &> /dev/null

cp -r web/app wp-content &> /dev/null
rm "wp-content/themes/${theme}/.gitignore" &> /dev/null
rm "wp-content/mu-plugins/bedrock-autoloader.php" &> /dev/null
rm "wp-content/mu-plugins/disallow-indexing.php" &> /dev/null
rm "wp-content/mu-plugins/register-theme-directory.php" &> /dev/null
rm .gitignore &> /dev/null
echo "/*\n!wp-content/\nwp-content/uploads" >> .gitignore
git ls-files | xargs git rm --cached &> /dev/null

cd wp-content/
find . | grep .git | xargs rm -rf
cd ../

git add . &> /dev/null
git commit -am "WPEngine build from: $(git log -1 HEAD --pretty=format:%s)$(git rev-parse --short HEAD 2> /dev/null | sed "s/\(.*\)/@\1/")" &> /dev/null
echo "Pushing to WPEngine..."
if [ "$environment" == "staging" ]
then
  git push staging wpengine:master --force &> /dev/null
  git checkout develop &> /dev/null
elif [ "$environment" == "production" ]
then
  git push production wpengine:master --force &> /dev/null
  git checkout master &> /dev/null
fi
git branch -D wpengine &> /dev/null
rm -rf wp-content/ &> /dev/null
echo "Successfully deployed."
