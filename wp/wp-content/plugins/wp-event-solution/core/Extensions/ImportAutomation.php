<?php

/**
 * Extentions class
 * 
 * @package Eventin
 */
namespace Eventin\Extensions;

defined( 'ABSPATH' ) || exit;

use Ens\Flow\Flow;
use Exception;

/**
 * Class extention
 */
class ImportAutomation {

    public static function create_automation_flows() {
        $result = array(
            'flow_ids' => array(),
            'errors'   => array(),
        );

        $automation_flows = [
            [
                "name" => "Send Email To All Attendees",
                "trigger" => "send_email_to_all_attendees",
                "flow_config" => [
                    "nodes" => [
                        [
                            "id" => "node_1",
                            "type" => "trigger",
                            "name" => "trigger",
                            "data" => [
                                "label" => "trigger: send_email_to_all_attendees",
                                "subtitle" => "Send Email To All Attendees",
                                "triggerValue" => "send_email_to_all_attendees"
                            ],
                            "position" => [
                                "x" => 200,
                                "y" => 50
                            ]
                        ],
                        [
                            "id" => "node_2",
                            "type" => "end",
                            "name" => "end",
                            "data" => [
                                "label" => "end_flow",
                                "subtitle" => "End execution path"
                            ],
                            "position" => [
                                "x" => 221.46762850701441,
                                "y" => 410
                            ]
                        ],
                        [
                            "id" => "node_3",
                            "type" => "action",
                            "name" => "email",
                            "data" => [
                                "actionType" => "send_email",
                                "label" => "send_email",
                                "subtitle" => "Subject: Thanks for Joining Us -  Here\u2019...",
                                "receiverType" => "attendee_email",
                                "from" => "admin@example.com",
                                "subject" => "Thanks for Joining Us -  Here\u2019s What\u2019s Next",
                                "body" => "<p>Hi Attendees ,<\/p><p>Thanks for attending \"{%event_title%}\". We appreciate your time and hope the session delivered real value.<\/p><p>&nbsp;<\/p><p>If you would like updates on future sessions or have feedback, reply directly to this email. we review every message.<\/p><p>&nbsp;<\/p><p>Thanks again,<\/p>",
                                "processed_session_ids" => []
                            ],
                            "position" => [
                                "x" => 210.73381425350721,
                                "y" => 230
                            ]
                        ]
                    ],
                    "edges" => [
                        [
                            "id" => "edge_node_1-node_3",
                            "type" => "smoothstep",
                            "markerEnd" => [
                                "type" => "arrowclosed"
                            ],
                            "source" => "node_1",
                            "target" => "node_3",
                            "data" => []
                        ],
                        [
                            "id" => "edge_node_3-node_2",
                            "type" => "smoothstep",
                            "markerEnd" => [
                                "type" => "arrowclosed"
                            ],
                            "source" => "node_3",
                            "target" => "node_2",
                            "data" => []
                        ]
                    ]
                ],
                "status" => "draft"
            ],
            [
                'name' => 'Purchase Email Automation For Attendee',
                'trigger' => 'event_ticket_purchase',
                'flow_config' => [
                    'nodes' => [
                        [
                            'id' => 'node_1',
                            'type' => 'trigger',
                            'name' => 'trigger',
                            'data' => [
                                'label' => 'trigger: event_ticket_purchase',
                                'subtitle' => 'Event Ticket Purchase',
                                'triggerValue' => 'event_ticket_purchase',
                            ],
                            'position' => [
                                'x' => 200,
                                'y' => 50,
                            ],
                        ],
                        [
                            'id' => 'node_2',
                            'type' => 'end',
                            'name' => 'end',
                            'data' => [
                                'label' => 'end_flow',
                                'subtitle' => 'End execution path',
                            ],
                            'position' => [
                                'x' => 200,
                                'y' => 410,
                            ],
                        ],
                        [
                            'id' => 'node_3',
                            'type' => 'action',
                            'name' => 'email',
                            'data' => [
                                'actionType' => 'send_email',
                                'label' => 'send_email',
                                'subtitle' => 'Subject: Attendee ticket purchase',
                                'receiverType' => 'attendee_email',
                                'from' => 'admin@gmail.com',
                                'subject' => 'You’ve Got Your Ticket!',
                                'body' => 'A ticket has been purchased for you. The details are as follows:',
                            ],
                            'position' => [
                                'x' => 200,
                                'y' => 230,
                            ],
                        ],
                    ],
                    'edges' => [
                        [
                            'id' => 'edge_node_1-node_3',
                            'type' => 'smoothstep',
                            'markerEnd' => [
                                'type' => 'arrowclosed',
                            ],
                            'source' => 'node_1',
                            'target' => 'node_3',
                            'data' => [],
                        ],
                        [
                            'id' => 'edge_node_3-node_2',
                            'type' => 'smoothstep',
                            'markerEnd' => [
                                'type' => 'arrowclosed',
                            ],
                            'source' => 'node_3',
                            'target' => 'node_2',
                            'data' => [],
                        ],
                    ],
                ],
                'status' => 'draft',
            ],
            [
                'name' => 'Purchase Email Automation For Customer',
                'trigger' => 'event_ticket_purchase',
                'flow_config' => [
                    'nodes' => [
                        [
                            'id' => 'node_1',
                            'type' => 'trigger',
                            'name' => 'trigger',
                            'data' => [
                                'label' => 'trigger: event_ticket_purchase',
                                'subtitle' => 'Event Ticket Purchase',
                                'triggerValue' => 'event_ticket_purchase',
                            ],
                            'position' => [
                                'x' => 200,
                                'y' => 50,
                            ],
                        ],
                        [
                            'id' => 'node_2',
                            'type' => 'end',
                            'name' => 'end',
                            'data' => [
                                'label' => 'end_flow',
                                'subtitle' => 'End execution path',
                            ],
                            'position' => [
                                'x' => 200,
                                'y' => 410,
                            ],
                        ],
                        [
                            'id' => 'node_3',
                            'type' => 'action',
                            'name' => 'email',
                            'data' => [
                                'actionType' => 'send_email',
                                'label' => 'send_email',
                                'subtitle' => 'Subject: Customer ticket purchase',
                                'receiverType' => 'customer_email',
                                'from' => 'admin@gmail.com',
                                'subject' => 'Ticket purchase successful',
                                'body' => 'You have purchased ticket(s). Attendee ticket details are as follows.',
                            ],
                            'position' => [
                                'x' => 200,
                                'y' => 230,
                            ],
                        ],
                    ],
                    'edges' => [
                        [
                            'id' => 'edge_node_1-node_3',
                            'type' => 'smoothstep',
                            'markerEnd' => [
                                'type' => 'arrowclosed',
                            ],
                            'source' => 'node_1',
                            'target' => 'node_3',
                            'data' => [],
                        ],
                        [
                            'id' => 'edge_node_3-node_2',
                            'type' => 'smoothstep',
                            'markerEnd' => [
                                'type' => 'arrowclosed',
                            ],
                            'source' => 'node_3',
                            'target' => 'node_2',
                            'data' => [],
                        ],
                    ],
                ],
                'status' => 'draft',
            ],
            [
                'name' => 'Purchase Email Automation For Admin',
                'trigger' => 'event_ticket_purchase',
                'flow_config' => [
                    'nodes' => [
                        [
                            'id' => 'node_1',
                            'type' => 'trigger',
                            'name' => 'trigger',
                            'data' => [
                                'label' => 'trigger: event_ticket_purchase',
                                'subtitle' => 'Event Ticket Purchase',
                                'triggerValue' => 'event_ticket_purchase',
                            ],
                            'position' => [
                                'x' => 200,
                                'y' => 50,
                            ],
                        ],
                        [
                            'id' => 'node_2',
                            'type' => 'end',
                            'name' => 'end',
                            'data' => [
                                'label' => 'end_flow',
                                'subtitle' => 'End execution path',
                            ],
                            'position' => [
                                'x' => 200,
                                'y' => 410,
                            ],
                        ],
                        [
                            'id' => 'node_3',
                            'type' => 'action',
                            'name' => 'email',
                            'data' => [
                                'actionType' => 'send_email',
                                'label' => 'send_email',
                                'subtitle' => 'Subject: Admin purchase confirmetion',
                                'receiverType' => 'admin_email',
                                'from' => 'admin@gmail.com',
                                'subject' => 'New Event Booking Received',
                                'body' => 'A customer has successfully purchased a ticket. Details are provided below:',
                            ],
                            'position' => [
                                'x' => 200,
                                'y' => 230,
                            ],
                        ],
                    ],
                    'edges' => [
                        [
                            'id' => 'edge_node_1-node_3',
                            'type' => 'smoothstep',
                            'markerEnd' => [
                                'type' => 'arrowclosed',
                            ],
                            'source' => 'node_1',
                            'target' => 'node_3',
                            'data' => [],
                        ],
                        [
                            'id' => 'edge_node_3-node_2',
                            'type' => 'smoothstep',
                            'markerEnd' => [
                                'type' => 'arrowclosed',
                            ],
                            'source' => 'node_3',
                            'target' => 'node_2',
                            'data' => [],
                        ],
                    ],
                ],
                'status' => 'draft',
            ],
            [
                'name' => 'RSVP Automation',
                'trigger' => 'event_rsvp_email',
                'flow_config' => [
                    'nodes' => [
                        [
                            'id' => 'node_1',
                            'type' => 'trigger',
                            'name' => 'trigger',
                            'data' => [
                                'label' => 'trigger: event_rsvp_email',
                                'subtitle' => 'RSVP Email',
                                'triggerValue' => 'event_rsvp_email',
                            ],
                            'position' => [
                                'x' => 200,
                                'y' => 50,
                            ],
                        ],
                        [
                            'id' => 'node_2',
                            'type' => 'end',
                            'name' => 'end',
                            'data' => [
                                'label' => 'end_flow',
                                'subtitle' => 'End execution path',
                            ],
                            'position' => [
                                'x' => 200,
                                'y' => 410,
                            ],
                        ],
                        [
                            'id' => 'node_3',
                            'type' => 'action',
                            'name' => 'email',
                            'data' => [
                                'actionType' => 'send_email',
                                'label' => 'send_email',
                                'subtitle' => 'Subject: RSVP ATTENDEES REMINDER',
                                'receiverType' => 'attendee_email',
                                'from' => 'admin@gmail.com',
                                'subject' => 'Event Reminder',
                                'body' => 'You have been invited to attend an event. The details are provided below:',
                            ],
                            'position' => [
                                'x' => 175.74853493583362,
                                'y' => 239.09429939906244,
                            ],
                        ],
                    ],
                    'edges' => [
                        [
                            'id' => 'edge_node_1-node_3',
                            'type' => 'smoothstep',
                            'markerEnd' => [
                                'type' => 'arrowclosed',
                            ],
                            'source' => 'node_1',
                            'target' => 'node_3',
                            'data' => [],
                        ],
                        [
                            'id' => 'edge_node_3-node_2',
                            'type' => 'smoothstep',
                            'markerEnd' => [
                                'type' => 'arrowclosed',
                            ],
                            'source' => 'node_3',
                            'target' => 'node_2',
                            'data' => [],
                        ],
                    ],
                ],
                'status' => 'draft',
            ],
            [
                'name' => 'Event Reminder',
                'trigger' => 'event_reminder_email',
                'flow_config' => [
                    'nodes' => [
                        [
                            'id' => 'node_1',
                            'type' => 'trigger',
                            'name' => 'trigger',
                            'data' => [
                                'label' => 'trigger: event_reminder_email',
                                'subtitle' => 'Event Reminder Email',
                                'triggerValue' => 'event_reminder_email',
                            ],
                            'position' => [
                                'x' => 200,
                                'y' => 50,
                            ],
                        ],
                        [
                            'id' => 'node_2',
                            'type' => 'end',
                            'name' => 'end',
                            'data' => [
                                'label' => 'end_flow',
                                'subtitle' => 'End execution path',
                            ],
                            'position' => [
                                'x' => 200,
                                'y' => 590,
                            ],
                        ],
                        [
                            'id' => 'node_3',
                            'type' => 'action',
                            'name' => 'delay',
                            'data' => [
                                'actionType' => 'add_delay',
                                'label' => 'add_delay',
                                'subtitle' => 'Wait for 10 minutes',
                                'delay' => 10,
                                'delayUnit' => 'minutes',
                                'delayCondition' => 'before_event_date',
                            ],
                            'position' => [
                                'x' => 200,
                                'y' => 230,
                            ],
                        ],
                        [
                            'id' => 'node_4',
                            'type' => 'action',
                            'name' => 'email',
                            'data' => [
                                'actionType' => 'send_email',
                                'label' => 'send_email',
                                'subtitle' => 'Subject: Just a friendly reminder about your upcoming event',
                                'receiverType' => 'attendee_email',
                                'from' => 'eventin@admin.com',
                                'subject' => 'Just a friendly reminder about your upcoming event',
                                'body' => 'This is a gentle reminder that your upcoming event is just around the corner!',
                            ],
                            'position' => [
                                'x' => 200,
                                'y' => 410,
                            ],
                        ],
                    ],
                    'edges' => [
                        [
                            'id' => 'edge_node_1-node_3',
                            'type' => 'smoothstep',
                            'markerEnd' => [
                                'type' => 'arrowclosed',
                            ],
                            'source' => 'node_1',
                            'target' => 'node_3',
                            'data' => [],
                        ],
                        [
                            'id' => 'edge_node_3-node_4',
                            'type' => 'smoothstep',
                            'markerEnd' => [
                                'type' => 'arrowclosed',
                            ],
                            'source' => 'node_3',
                            'target' => 'node_4',
                            'data' => [],
                        ],
                        [
                            'id' => 'edge_node_4-node_2',
                            'type' => 'smoothstep',
                            'markerEnd' => [
                                'type' => 'arrowclosed',
                            ],
                            'source' => 'node_4',
                            'target' => 'node_2',
                            'data' => [],
                        ],
                    ],
                ],
                'status' => 'draft',
            ]
        ];
        try {
            foreach ( $automation_flows as $key => $automation_flow ) {                
                $flow = new Flow( 'eve',0 );
                $flow->set_props( $automation_flow );
                $flow_id = $flow->save();
                if ( $flow_id ) {
                    $result['flow_ids'][] = $flow_id;
                }
            }

            update_option( 'etn_email_automation_migrated', true );           
        } catch ( Exception $e ) {
            $result['errors'][] = 'Failed to create service: ' . $e->getMessage();
        }

        return $result;
    }

    /**
     * update automation flow
     */
    public static function update_automation_flows() {
        $result = array(
            'flow_ids' => array(),
            'errors'   => array(),
        );

        $automation_flows = [
            [
                "name" => "Send Email To All Attendees",
                "trigger" => "send_email_to_all_attendees",
                "flow_config" => [
                    "nodes" => [
                        [
                            "id" => "node_1",
                            "type" => "trigger",
                            "name" => "trigger",
                            "data" => [
                                "label" => "trigger: send_email_to_all_attendees",
                                "subtitle" => "Send Email To All Attendees",
                                "triggerValue" => "send_email_to_all_attendees"
                            ],
                            "position" => [
                                "x" => 200,
                                "y" => 50
                            ]
                        ],
                        [
                            "id" => "node_2",
                            "type" => "end",
                            "name" => "end",
                            "data" => [
                                "label" => "end_flow",
                                "subtitle" => "End execution path"
                            ],
                            "position" => [
                                "x" => 221.46762850701441,
                                "y" => 410
                            ]
                        ],
                        [
                            "id" => "node_3",
                            "type" => "action",
                            "name" => "email",
                            "data" => [
                                "actionType" => "send_email",
                                "label" => "send_email",
                                "subtitle" => "Subject: Thanks for Joining Us -  Here\u2019...",
                                "receiverType" => "attendee_email",
                                "from" => "admin@example.com",
                                "subject" => "Thanks for Joining Us -  Here\u2019s What\u2019s Next",
                                "body" => "<p>Hi Attendees ,<\/p><p>Thanks for attending \"{%event_title%}\". We appreciate your time and hope the session delivered real value.<\/p><p>&nbsp;<\/p><p>If you would like updates on future sessions or have feedback, reply directly to this email. we review every message.<\/p><p>&nbsp;<\/p><p>Thanks again,<\/p>",
                                "processed_session_ids" => []
                            ],
                            "position" => [
                                "x" => 210.73381425350721,
                                "y" => 230
                            ]
                        ]
                    ],
                    "edges" => [
                        [
                            "id" => "edge_node_1-node_3",
                            "type" => "smoothstep",
                            "markerEnd" => [
                                "type" => "arrowclosed"
                            ],
                            "source" => "node_1",
                            "target" => "node_3",
                            "data" => []
                        ],
                        [
                            "id" => "edge_node_3-node_2",
                            "type" => "smoothstep",
                            "markerEnd" => [
                                "type" => "arrowclosed"
                            ],
                            "source" => "node_3",
                            "target" => "node_2",
                            "data" => []
                        ]
                    ]
                ],
                "status" => "draft"
            ],
        ];
        try {
            foreach ( $automation_flows as $key => $automation_flow ) {                
                $flow = new Flow( 'eve',0 );
                $flow->set_props( $automation_flow );
                $flow_id = $flow->save();
                if ( $flow_id ) {
                    $result['flow_ids'][] = $flow_id;
                }
            }

        } catch ( Exception $e ) {
            $result['errors'][] = 'Failed to create service: ' . $e->getMessage();
        }

        return $result;
    }
}
