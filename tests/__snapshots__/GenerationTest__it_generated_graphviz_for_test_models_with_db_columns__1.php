<?php return 'Found 4 models.
Inspecting model relations.

 1/4 [▓▓▓▓▓▓▓░░░░░░░░░░░░░░░░░░░░░]  25%
 2/4 [▓▓▓▓▓▓▓▓▓▓▓▓▓▓░░░░░░░░░░░░░░]  50%
 3/4 [▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓░░░░░░░]  75%
 4/4 [▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓] 100%digraph "G" {
style="filled"
bgcolor="#F7F7F7"
fontsize="12"
labelloc="t"
concentrate="1"
splines="polyline"
overlap=""
nodesep="1"
rankdir="LR"
pad="0.5"
ranksep="2"
esep="1"
fontname="Helvetica Neue"
beyondcodeerdgeneratortestsmodelsavatar:user_id -> beyondcodeerdgeneratortestsmodelsuser:id [
label=" "
xlabel="BelongsTo
user"
color="#F77F00"
penwidth="1.8"
fontname="Helvetica Neue"
dir="both"
arrowhead="tee"
arrowtail="crow"
]
beyondcodeerdgeneratortestsmodelscomment:user_id -> beyondcodeerdgeneratortestsmodelsuser:id [
label=" "
xlabel="BelongsTo
user"
color="#F77F00"
penwidth="1.8"
fontname="Helvetica Neue"
dir="both"
arrowhead="tee"
arrowtail="crow"
]
beyondcodeerdgeneratortestsmodelspost:user_id -> beyondcodeerdgeneratortestsmodelsuser:id [
label=" "
xlabel="BelongsTo
user"
color="#F77F00"
penwidth="1.8"
fontname="Helvetica Neue"
dir="both"
arrowhead="tee"
arrowtail="crow"
]
beyondcodeerdgeneratortestsmodelspost:id -> beyondcodeerdgeneratortestsmodelscomment:post_id [
label=" "
xlabel="HasMany
comments"
color="#FCBF49"
penwidth="1.8"
fontname="Helvetica Neue"
dir="both"
arrowhead="crow"
arrowtail="none"
]
beyondcodeerdgeneratortestsmodelsuser:id -> beyondcodeerdgeneratortestsmodelspost:user_id [
label=" "
xlabel="HasMany
posts"
color="#FCBF49"
penwidth="1.8"
fontname="Helvetica Neue"
dir="both"
arrowhead="crow"
arrowtail="none"
]
beyondcodeerdgeneratortestsmodelsuser:id -> beyondcodeerdgeneratortestsmodelsavatar:user_id [
label=" "
xlabel="HasOne
avatar"
color="#D62828"
penwidth="1.8"
fontname="Helvetica Neue"
dir="both"
arrowhead="tee"
arrowtail="none"
]
beyondcodeerdgeneratortestsmodelsuser:id -> comment_user:user_id [
label=" "
xlabel="BelongsToMany
comments"
color="#003049"
penwidth="1.8"
fontname="Helvetica Neue"
]
comment_user:comment_id -> beyondcodeerdgeneratortestsmodelscomment:id [
label=" "
xlabel="BelongsToMany
comments"
color="#003049"
penwidth="1.8"
fontname="Helvetica Neue"
]
"beyondcodeerdgeneratortestsmodelsavatar" [
label=<<table width="100%" height="100%" border="0" margin="0" cellborder="1" cellspacing="0" cellpadding="10">
<tr width="100%"><td width="100%" bgcolor="#d3d3d3"><font color="#333333">Avatar</font></td></tr>
<tr width="100%"><td port="id" align="left" width="100%"  bgcolor="#ffffff"><font color="#333333" >id</font></td></tr>
<tr width="100%"><td port="url" align="left" width="100%"  bgcolor="#ffffff"><font color="#333333" >url</font></td></tr>
<tr width="100%"><td port="created_at" align="left" width="100%"  bgcolor="#ffffff"><font color="#333333" >created_at</font></td></tr>
<tr width="100%"><td port="updated_at" align="left" width="100%"  bgcolor="#ffffff"><font color="#333333" >updated_at</font></td></tr>
</table>>
margin="0"
shape="rectangle"
fontname="Helvetica Neue"
]
"beyondcodeerdgeneratortestsmodelscomment" [
label=<<table width="100%" height="100%" border="0" margin="0" cellborder="1" cellspacing="0" cellpadding="10">
<tr width="100%"><td width="100%" bgcolor="#d3d3d3"><font color="#333333">Comment</font></td></tr>
<tr width="100%"><td port="id" align="left" width="100%"  bgcolor="#ffffff"><font color="#333333" >id</font></td></tr>
<tr width="100%"><td port="post_id" align="left" width="100%"  bgcolor="#ffffff"><font color="#333333" >post_id</font></td></tr>
<tr width="100%"><td port="body" align="left" width="100%"  bgcolor="#ffffff"><font color="#333333" >body</font></td></tr>
<tr width="100%"><td port="commentable_type" align="left" width="100%"  bgcolor="#ffffff"><font color="#333333" >commentable_type</font></td></tr>
<tr width="100%"><td port="commentable_id" align="left" width="100%"  bgcolor="#ffffff"><font color="#333333" >commentable_id</font></td></tr>
</table>>
margin="0"
shape="rectangle"
fontname="Helvetica Neue"
]
"beyondcodeerdgeneratortestsmodelspost" [
label=<<table width="100%" height="100%" border="0" margin="0" cellborder="1" cellspacing="0" cellpadding="10">
<tr width="100%"><td width="100%" bgcolor="#d3d3d3"><font color="#333333">Post</font></td></tr>
<tr width="100%"><td port="id" align="left" width="100%"  bgcolor="#ffffff"><font color="#333333" >id</font></td></tr>
<tr width="100%"><td port="user_id" align="left" width="100%"  bgcolor="#ffffff"><font color="#333333" >user_id</font></td></tr>
<tr width="100%"><td port="title" align="left" width="100%"  bgcolor="#ffffff"><font color="#333333" >title</font></td></tr>
<tr width="100%"><td port="body" align="left" width="100%"  bgcolor="#ffffff"><font color="#333333" >body</font></td></tr>
<tr width="100%"><td port="created_at" align="left" width="100%"  bgcolor="#ffffff"><font color="#333333" >created_at</font></td></tr>
<tr width="100%"><td port="updated_at" align="left" width="100%"  bgcolor="#ffffff"><font color="#333333" >updated_at</font></td></tr>
</table>>
margin="0"
shape="rectangle"
fontname="Helvetica Neue"
]
"beyondcodeerdgeneratortestsmodelsuser" [
label=<<table width="100%" height="100%" border="0" margin="0" cellborder="1" cellspacing="0" cellpadding="10">
<tr width="100%"><td width="100%" bgcolor="#d3d3d3"><font color="#333333">User</font></td></tr>
<tr width="100%"><td port="id" align="left" width="100%"  bgcolor="#ffffff"><font color="#333333" >id</font></td></tr>
<tr width="100%"><td port="avatar_id" align="left" width="100%"  bgcolor="#ffffff"><font color="#333333" >avatar_id</font></td></tr>
<tr width="100%"><td port="name" align="left" width="100%"  bgcolor="#ffffff"><font color="#333333" >name</font></td></tr>
<tr width="100%"><td port="email" align="left" width="100%"  bgcolor="#ffffff"><font color="#333333" >email</font></td></tr>
<tr width="100%"><td port="created_at" align="left" width="100%"  bgcolor="#ffffff"><font color="#333333" >created_at</font></td></tr>
<tr width="100%"><td port="updated_at" align="left" width="100%"  bgcolor="#ffffff"><font color="#333333" >updated_at</font></td></tr>
</table>>
margin="0"
shape="rectangle"
fontname="Helvetica Neue"
]
"comment_user" [
label=<<table width="100%" height="100%" border="0" margin="0" cellborder="1" cellspacing="0" cellpadding="10">
<tr width="100%"><td width="100%" bgcolor="#d3d3d3"><font color="#333333">Pivot</font></td></tr>
<tr width="100%"><td port="id" align="left" width="100%"  bgcolor="#ffffff"><font color="#333333" >id</font></td></tr>
<tr width="100%"><td port="comment_id" align="left" width="100%"  bgcolor="#ffffff"><font color="#333333" >comment_id</font></td></tr>
<tr width="100%"><td port="user_id" align="left" width="100%"  bgcolor="#ffffff"><font color="#333333" >user_id</font></td></tr>
</table>>
margin="0"
shape="rectangle"
fontname="Helvetica Neue"
]
}
';
