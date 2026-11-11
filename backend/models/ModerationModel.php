
<?php

class ModerationModel extends Model {
    
    public function getItemsByStatus(string $statusFilter): array {
        $sql = "
            SELECT * FROM (
                -- Pending / Approved / Rejected Resources
                SELECT 
                    'Resource' AS type,
                    r.resource_id AS id,
                    CASE 
                        WHEN r.status = 'pending' THEN 'pending'
                        WHEN r.status = 'approved' THEN 'kept'
                        WHEN r.status = 'rejected' THEN 'hidden'
                    END AS status,
                    r.title,
                    r.description AS content,
                    c.course_name AS course,
                    u.full_name AS author,
                    NULL AS reason,
                    NULL AS flaggedBy,
                    NULL AS flaggedAt,
                    r.created_at AS sort_date
                FROM resources r
                JOIN users u ON r.uploader_id = u.user_id
                JOIN courses c ON r.course_id = c.course_id
                
                UNION ALL
                
                -- Flagged Discussions (Joined with Reports)
                SELECT 
                    'Discussion' AS type,
                    d.discussion_id AS id,
                    CASE 
                        WHEN d.status = 'removed' THEN 'hidden'
                        WHEN rep.status = 'pending' THEN 'pending'
                        ELSE 'kept'
                    END AS status,
                    d.title,
                    d.content,
                    c.course_name AS course,
                    u.full_name AS author,
                    rep.reason,
                    ru.full_name AS flaggedBy,
                    rep.created_at AS flaggedAt,
                    rep.created_at AS sort_date
                FROM discussions d
                JOIN users u ON d.author_id = u.user_id
                JOIN courses c ON d.course_id = c.course_id
                JOIN reports rep ON rep.target_type = 'Discussion' AND rep.target_id = d.discussion_id
                JOIN users ru ON rep.reporter_id = ru.user_id
                
                UNION ALL
                
                -- Flagged Replies (Joined with Reports and Discussions for Title)
                SELECT 
                    'Reply' AS type,
                    dr.reply_id AS id,
                    CASE 
                        WHEN dr.status = 'removed' THEN 'hidden'
                        WHEN rep.status = 'pending' THEN 'pending'
                        ELSE 'kept'
                    END AS status,
                    d.title,
                    dr.content,
                    c.course_name AS course,
                    u.full_name AS author,
                    rep.reason,
                    ru.full_name AS flaggedBy,
                    rep.created_at AS flaggedAt,
                    rep.created_at AS sort_date
                FROM discussion_replies dr
                JOIN discussions d ON dr.discussion_id = d.discussion_id
                JOIN users u ON dr.author_id = u.user_id
                JOIN courses c ON d.course_id = c.course_id
                JOIN reports rep ON rep.target_type = 'Reply' AND rep.target_id = dr.reply_id
                JOIN users ru ON rep.reporter_id = ru.user_id
            ) AS moderation_queue
        ";

        $params = [];
        
        if ($statusFilter !== 'all') {
            $sql .= " WHERE status = ? ";
            $params[] = $statusFilter;
        }
        
        $sql .= " ORDER BY sort_date DESC";

        return $this->fetchAll($sql, $params);
    }

    public function updateStatus(string $type, int $id, string $statusAction): bool {
        // Resources get approved or rejected
        if ($type === 'Resource') {
            $dbStatus = match($statusAction) {
                'kept'   => 'approved',
                'hidden' => 'rejected',
                default  => 'pending'
            };
            $this->execute("UPDATE resources SET status = ? WHERE resource_id = ?", [$dbStatus, $id]);
            return true;
        }

        // Discussions and Replies need the item hidden/shown and the associated report resolved/dismissed
        if ($type === 'Discussion' || $type === 'Reply') {
            $table = ($type === 'Discussion') ? 'discussions' : 'discussion_replies';
            $idCol = ($type === 'Discussion') ? 'discussion_id' : 'reply_id';
            
            // Update the post visibility
            $itemStatus = ($statusAction === 'hidden') ? 'removed' : 'visible';
            $this->execute("UPDATE {$table} SET status = ? WHERE {$idCol} = ?", [$itemStatus, $id]);

            // Clear out the reports so they don't stay pending
            $reportStatus = match($statusAction) {
                'kept'   => 'dismissed', // Post was fine, dismiss the report
                'hidden' => 'resolved',  // Post was bad, report is resolved
                default  => 'pending'
            };
            
            $this->execute(
                "UPDATE reports SET status = ? WHERE target_type = ? AND target_id = ?", 
                [$reportStatus, $type, $id]
            );
            
            return true;
        }

        return false;
    }
}
