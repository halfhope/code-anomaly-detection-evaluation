<?

require('../app/VK/VK.php');

define('APP_ID', 5826191);
define('APP_SECRET', 'iE7tQNHEUC66VKOudeBu');

$targetUser1 = 87580683;
$targetUser2 = 292653561;

function array_flatten($array) {
    if (!is_array($array)) {
        return FALSE;
    }
    $result = array();
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            $result = array_merge($result, array_flatten($value));
        } else {
            $result[$key] = $value;
        }
    }
    return $result;
}

function getFriends($vk, $userId) {
    $result = $vk->api('friends.get', array(
        'user_id' => $userId
    ));
    usleep(350000);
    return $result['response'];
}

function isMutualFriends($vk, $userId1, $userIds2) {
    $isMultiple = is_array($userIds2);
    $targetVarName = $isMultiple ? 'target_uids' : 'target_uid';
    if ($isMultiple) {
        $userIds2 = implode(',', $userIds2);
    }
    $result = $vk->api('friends.getMutual', array(
        'source_uid' => $userId1,
        $targetVarName => $userIds2
    ));
    usleep(350000);
    return $result['response'];
}

function getCommonFriends($commonFriends) {
    $commonFriendsNormalize = array();
    if (empty($commonFriends)) {
        return $commonFriendsNormalize;
    }
    foreach ($commonFriends as $friend) {
        if ($friend['common_count'] != 0) {
            $commonFriendsNormalize[$friend['id']] = $friend['common_friends'];
        }
    }
    return $commonFriendsNormalize;
}

function filteringDeletedUser($vk, $usersOriginal) {
    $result = $vk->api('users.get', array(
        'user_ids' => implode(',', $usersOriginal)
    ));
    usleep(350000);
    $users = $result['response'];
    if (!is_array($users)) {
        print_r($usersOriginal);
    }
    $users = array_filter($users, function($user) {
        return empty($user['deactivated']);
    });
    $userOutput = array();
    foreach ($users as $user) {
        $userOutput[] = $user['uid'];
    }
    return $userOutput;
}

$accessToken = 'd0b27aebcf614b74c27f449e821fdcf951a85eb4bd106560705634ab767a3aadb37b45f42bbea7e3a2374';

$vk = new VK\VK(APP_ID, APP_SECRET, $accessToken);

$mutualFriends = isMutualFriends($vk, $targetUser1, $targetUser2);

if (count($mutualFriends) == 0) {
    $friends1 = getFriends($vk, $targetUser1);
    $friends1Filtering = array_chunk($friends1, 100);
    foreach($friends1Filtering as &$friends1PartFiltering) {
        $friends1PartFiltering = filteringDeletedUser($vk, $friends1PartFiltering);
    }
    $friends1 = array_flatten($friends1Filtering);
    $friends2 = getFriends($vk, $targetUser2);
    $friends2Filtering = array_chunk($friends2, 100);
    foreach($friends2Filtering as &$friends2PartFiltering) {
        $friends2PartFiltering = filteringDeletedUser($vk, $friends2PartFiltering);
    }
    $friends2 = array_flatten($friends2Filtering);
    $friends2 = array_chunk($friends2, 100);
    $i = 0;
    foreach($friends1 as $friend1) {
        $i++;
        $j = 0;
        foreach($friends2 as $friendsPart1) {
            $j++;
            $commonFriendsOriginal = isMutualFriends($vk, $friend1, $friendsPart1);
            $commonFriends = getCommonFriends($commonFriendsOriginal);
            if (count($commonFriends) > 0) {
                echo 'Common friends detected!' . PHP_EOL;
                $commonFriendsOriginal = $commonFriends;
                reset($commonFriends);
                $first_uid = key($commonFriends);
                $intermediateFriend = $commonFriendsOriginal[$first_uid][0];
                echo "$targetUser2 - $first_uid - $intermediateFriend - $friend1 - $targetUser1" . PHP_EOL;
                break 2;
            }
            echo "$i attempt ($j portion): no common friends." . PHP_EOL;
        }
    }
} else {
    echo 'Common friends detected!';
}

//$friends1 = getFriends($vk, $targetUser1);
//$friends2 = getFriends($vk, $targetUser2);

//walkFriend($vk, $targetUser1, 0);

//walkFriend($vk, $targetUser2, 0);
