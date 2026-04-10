<?php

namespace Eventin\Blocks;

defined( 'ABSPATH' ) || exit;

use Eventin\Blocks\BlockTypes\AttendeeExtraFields;
use Eventin\Blocks\BlockTypes\AttendeeInfo;
use Eventin\Blocks\BlockTypes\BuyTicket;
use Eventin\Blocks\BlockTypes\Container;
use Eventin\Blocks\BlockTypes\CustomButton;
use Eventin\Blocks\BlockTypes\CustomImage;
use Eventin\Blocks\BlockTypes\DiamondSeparator;
use Eventin\Blocks\BlockTypes\EventAddToCalender;
use Eventin\Blocks\BlockTypes\EventAttendee;
use Eventin\Blocks\BlockTypes\EventBanner;
use Eventin\Blocks\BlockTypes\EventCalendar;
use Eventin\Blocks\BlockTypes\EventCategory;
use Eventin\Blocks\BlockTypes\EventCountDownTimer;
use Eventin\Blocks\BlockTypes\EventDateTime;
use Eventin\Blocks\BlockTypes\EventDescription;
use Eventin\Blocks\BlockTypes\EventFaq;
use Eventin\Blocks\BlockTypes\EventInfo;
use Eventin\Blocks\BlockTypes\EventList;
use Eventin\Blocks\BlockTypes\EventLogo;
use Eventin\Blocks\BlockTypes\EventOrganizer;
use Eventin\Blocks\BlockTypes\EventRSVP;
use Eventin\Blocks\BlockTypes\EventSchedule;
use Eventin\Blocks\BlockTypes\EventSocial;
use Eventin\Blocks\BlockTypes\EventSpeaker;
use Eventin\Blocks\BlockTypes\EventTag;
use Eventin\Blocks\BlockTypes\EventTitle;
use Eventin\Blocks\BlockTypes\EventVenue;
use Eventin\Blocks\BlockTypes\QRCodeBlock;
use Eventin\Blocks\BlockTypes\RecurringEvent;
use Eventin\Blocks\BlockTypes\RelatedEventsEnhanced;
use Eventin\Blocks\BlockTypes\ScheduleTab;
use Eventin\Blocks\BlockTypes\SpeakerList;
use Eventin\Blocks\BlockTypes\TemplateContainer;
use Eventin\Blocks\BlockTypes\TemplateHeading;
use Eventin\Blocks\BlockTypes\Ticket;
use Eventin\Blocks\BlockTypes\TicketInfo;
use Eventin\Blocks\BlockTypes\ZoomMeeting;
use Eventin\Interfaces\HookableInterface;

/**
 * Block Service Class
 */
class BlockService implements HookableInterface
{
    /**
     * Register all hooks
     *
     * @return  void
     */
    public function register_hooks(): void
    {
        add_filter('eventin_gutenberg_blocks', [$this, 'add_blocks'], 5);
    }

    /**
     * Added blocks
     *
     * @return  array
     */
    public function add_blocks($blocks)
    {
        $new_blocks = [
            EventVenue::class,
            BuyTicket::class,
            RelatedEventsEnhanced::class,
            EventLogo::class,
            EventFaq::class,
            EventRSVP::class,
            EventSpeaker::class,
            EventOrganizer::class,
            EventBanner::class,
            EventDateTime::class,
            EventTag::class,
            EventCategory::class,
            EventSchedule::class,
            RecurringEvent::class,
            EventTitle::class,
            EventDescription::class,
            EventAddToCalender::class,
            EventSocial::class,
            EventCountDownTimer::class,
            EventList::class,
            EventCalendar::class,
            SpeakerList::class,
            ZoomMeeting::class,
            Ticket::class,
            ScheduleTab::class,
            QRCodeBlock::class,
            TemplateContainer::class,
            TemplateHeading::class,
            DiamondSeparator::class,
            AttendeeInfo::class,
            AttendeeExtraFields::class,
            EventInfo::class,
            TicketInfo::class,
            Container::class,
            CustomImage::class,
            CustomButton::class,
            EventAttendee::class,
        ];

        return array_unique(array_merge($blocks, $new_blocks));
    }
}
