environment=$1
theme="sage"


echo "Starting the build process..."
if [ "$environment" == "staging" ]
then
  git checkout develop &> /dev/null
elif [ "$environment" == "production" ]
then
  git checkout master &> /dev/null
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

cp -r web/app wp-content
rm "wp-content/themes/${theme}/.gitignore"
rm "wp-content/mu-plugins/bedrock-autoloader.php"
rm "wp-content/mu-plugins/disallow-indexing.php"
rm "wp-content/mu-plugins/register-theme-directory.php"
rm .gitignore
echo "/*\n!wp-content/\nwp-content/uploads" >> .gitignore
git ls-files | xargs git rm --cached &> /dev/null

cd wp-content/
find . | grep .git | xargs rm -rf
cd ../

git add . &> /dev/null
git commit -am "Setting up WPEngine build." &> /dev/null
if [ "$environment" == "staging" ]
then
  git push staging wpengine:master --force
  git checkout develop &> /dev/null
elif [ "$environment" == "production" ]
then
  git push production wpengine:master --force
  git checkout master &> /dev/null
fi
git branch -D wpengine &> /dev/null
rm -rf wp-content/ &> /dev/null
