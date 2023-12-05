# socialFeed
socialfeed extra for MODX CMS. The extra is an service to show your social Channels like Instagram or TikTok on your MODX Website without struggling with the APIs of the social media sites. Just setup your Feed on www.socialfeed.pro, connect your social channels and install the socialFeed extra to show all your media on your site.

## Benefits
- Easy integration without any API-knowledge
- Multi channels: Add as many channels to your website as you want
- Multi Feed-Setup to manage all your clients websites
- Customer friendly: Just send a link to your client to let him easily connect his account to your Feed. You never have to ask for login credentials again.

## Setup
To get your Feed up and running:

### Step 1: Setup your Feed
1. register your account on www.socialfeed.pro
2. Setup a Feed (Create one Feed for each of your Projects)
3. Add an channel to your Feed (For example an instagram account)

### Step 2: Integration in MODX
4. Install the socialFeed extra via the Package-Manager.
5. Get the API-Credentials of your Feed you set up on www.socialfeed.pro (API-KEY, FEED-KEY, FEED-ID)
6. Go to system-settings of the namespace "socialFeed" and fill in your API-Credentials (API-KEY, FEED-KEY, FEED-ID)
7. Go to www.yourdomain.de/assets/components/socialfeed/cron.php to import the latest media of your feed
8. Setup an cron-job to call ww.yourdomain.de/assets/components/socialfeed/cron.php frequently to get the latest media. (If your Hosting-Privider does not support cron-jobs, here is a free service: https://cron-job.org)
9. Place the socialFeed snippet into your code. DONE!

## Example
socialFeed has an internal caching. You can call it uncached.
```
[[!socialFeed]]
```

## Properties
| setting | default | description |
| --- | --- | --- |
| &tpl | socialFeedTpl | Customize your layout with your indiviuell chunk. |
| &limit | www | Limit your media quantity. |
| &offset | 0 | Offset your media to use with pagination for example. |
| &sortby | date |  |
| &sortdir | desc | Option: desc/asc |
| &filterUser |  | Filter media by username. Usefull if you have different channels in your feed and you want to show different accounts in different sections of your site. |
| &filterContent |  | Filter media by string in content. For example: #youtag to filter media by hastags. |
| &filterChannelType |  | Filter media by channel type. For example: "tiktok" to show only media from a tiktok channel. Or "instagram,tiktok" to show media from Tiktok and Instagram Channels. |
| &cache | true | Option: True/False to enable/disable caching. |
| &cacheTime | 3600 | Seconds to refresh Cache. You can actually set this up very high because your cron-job will clear the cache if there is new media available. |
| &cacheKey | socialFeed | Cahing-Key |

## Placeholders: &tpl
| tag | description |
| --- | --- |
| id | ID |
| idx | Increasing Number |
| key | socialFeed DB reference |
| username | ID |
| channel | Channel Type: instagram / tiktok / ... |
| type | Media-Type: IMAGE / VIDEO / EMBED |
| image | URL to the image thumb |
| url | URL to the Media-File Image/Video |
| permalink | URL to the post/website |
| content | Content of the media |
| date | Date of Publishing the media |
| properties | properties can be called by prefixing them: +properties.yourname |

### Properties: TikTok
like_count, comment_count, share_count, view_count, width, height, title
