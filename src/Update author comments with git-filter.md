git filter-branch --env-filter 'if [ "$GIT_AUTHOR_EMAIL" = "l.ridley@vardot.com" ]; then
     GIT_AUTHOR_EMAIL=lisa@codementality.com;
     GIT_AUTHOR_NAME="Lisa Ridley";
     GIT_COMMITTER_EMAIL=$GIT_AUTHOR_EMAIL;
     GIT_COMMITTER_NAME="$GIT_AUTHOR_NAME"; fi' -- --all