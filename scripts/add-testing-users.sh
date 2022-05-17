#!/bin/usr/env bash

current_path=$(pwd);

# Include the Bash YAML library.
source ${current_path}/libs/bash-yaml.sh || exit 1 ;

# Load the list of default users for Cucumber.
eval $(parse_yaml ${current_path}/cucumber.users.yml);

cd ../../../../../;

# Add Drush if it was not in the system.
if [ ! -d "vendor/drush/drush" ]; then
  echo "Add Drush if it was not in the system.";
  composer require "drush/drush:~11.0";
fi

cd web;

for user in ${users_[@]}
do
    user_name="user_${!user}_name";
    user_mail="user_${!user}_mail";
    user_password="user_${!user}_password";
    user_role="user_${!user}_role";

    echo " ---------------------------------------------------------------- ";
    echo "      User name: ${!user_name}";
    echo "      User mail: ${!user_mail}";
    echo "  User password: ${!user_password}";
    echo "      User role: ${!user_role}";
    echo " ================================================================= ";

    ../bin/drush user:create "${!user_name}" --mail="${!user_mail}" --password="${!user_password}" ;
    if [ "${!user_role}" == '_none_' ] ; then
        echo "   No user role for this user" ;
    else
        ../bin/drush user:role:add "${!user_role}" "${!user_name}" ;
    fi
done

echo "Cache rebuilding ...";
../bin/drush cache:rebuild ;
