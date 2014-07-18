Feature: Manage WordPress posts

  Background:
    Given a WP install

  Scenario: Creating/updating/deleting posts
    When I run `wp post create --post_title='Test post' --porcelain`
    Then STDOUT should be a number
    And save STDOUT as {POST_ID}

    When I run `wp post update {POST_ID} --post_title='Updated post'`
    Then STDOUT should be:
      """
      Success: Updated post {POST_ID}.
      """

    When I run `wp post delete {POST_ID}`
    Then STDOUT should be:
      """
      Success: Trashed post {POST_ID}.
      """

    When I run the previous command again
    Then STDOUT should not be empty

    When I try the previous command again
    Then the return code should be 1

  Scenario: Creating/getting/editing posts
    Given a content.html file:
      """
      This is some content.

      <script>
      alert('This should not be stripped.');
      </script>
      """
    And a command.sh file:
      """
      cat content.html | wp post create --post_title='Test post' --post_excerpt="A multiline
      excerpt" --porcelain -
      """

    When I run `bash command.sh`
    Then STDOUT should be a number
    And save STDOUT as {POST_ID}

    When I run `wp post get --field=excerpt {POST_ID}`
    Then STDOUT should be:
      """
      A multiline
      excerpt
      """

    When I run `wp post get --field=content {POST_ID} | diff -Bu content.html -`
    Then STDOUT should be empty

    When I run `wp post get --format=table {POST_ID}`
    Then STDOUT should be a table containing rows:
      | Field      | Value     |
      | ID         | {POST_ID} |
      | post_title | Test post |

    When I run `wp post get --format=json {POST_ID}`
    Then STDOUT should be JSON containing:
      """
      {
        "ID": {POST_ID},
        "post_title": "Test post"
      }
      """

    When I try `EDITOR='ex -i NONE -c q!' wp post edit {POST_ID}`
    Then STDERR should contain:
      """
      No change made to post content.
      """
    And the return code should be 0

    When I try `EDITOR='ex -i NONE -c %s/content/bunkum -c wq' wp post edit {POST_ID}`
    Then STDERR should be empty
    Then STDOUT should contain:
      """
      Updated post {POST_ID}.
      """

    When I run `wp post get --field=content {POST_ID}`
    Then STDOUT should contain:
      """
      This is some bunkum.
      """
    
    When I run `wp post url 1 {POST_ID}`
    Then STDOUT should be:
      """
      http://example.com/?p=1
      http://example.com/?p=3
      """


  Scenario: Creating/listing posts
    When I run `wp post create --post_title='Publish post' --post_content='Publish post content' --post_status='publish' --porcelain`
    Then STDOUT should be a number
    And save STDOUT as {POST_ID}

    When I run `wp post create --post_title='Draft post' --post_content='Draft post content' --post_status='draft' --porcelain`
    Then STDOUT should be a number

    When I run `wp post list --post_type='post' --fields=post_title,post_name,post_status --format=csv`
    Then STDOUT should be CSV containing:
      | post_title   | post_name    | post_status  |
      | Publish post | publish-post | publish      |
      | Draft post   |              | draft        |

    When I run `wp post list --post_type='post' --fields=title,name,status --format=csv`
    Then STDOUT should be CSV containing:
      | post_title   | post_name    | post_status  |
      | Publish post | publish-post | publish      |
      | Draft post   |              | draft        |

    When I run `wp post list --post__in={POST_ID} --format=count`
    Then STDOUT should be:
      """
      1
      """

    When I run `wp post list --post_type='page' --field=title`
    Then STDOUT should be:
      """
      Sample Page
      """

  Scenario: Generating posts
    When I run `echo "Content generated by wp post generate" | wp post generate --count=1 --post_content`
    And I run `wp post list --field=post_content`
    Then STDOUT should contain:
      """
      Content generated by wp post generate
      """
    And STDERR should be empty

  Scenario: Generating posts by a specific author

    When I run `wp user create dummyuser dummy@example.com --porcelain`
    Then save STDOUT as {AUTHOR_ID}

    When I run `wp post generate --post_author={AUTHOR_ID} --post_type=post --count=16`
    And I run `wp post list --post_type=post --author={AUTHOR_ID} --format=count`
    Then STDOUT should contain:
      """
      16
      """

  Scenario: Generating pages
    When I run `wp post generate --post_type=page --max_depth=10`
    And I run `wp post list --post_type=page --field=post_parent`
    Then STDOUT should contain:
      """
      1
      """