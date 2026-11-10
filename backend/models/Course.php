<?php

// Model for the courses table (Course Management, plus course lists on other pages).
// Courses are never deleted, only archived, because slots, discussions and resources
// still point to them. Archived courses are hidden from search and booking.
class Course extends Model
{
    // Active courses only, for dropdowns like the Find a Tutor course filter
    public function active(): array
    {
        return $this->fetchAll('SELECT * FROM courses WHERE status = "active" ORDER BY course_code');
    }

    // All courses for the admin table, with how many tutors teach each one.
    // LEFT JOIN keeps courses that have 0 tutors (COUNT then gives 0).
    // Active courses are listed first because "active" comes before "archived" alphabetically.
    public function allWithTutorCount(): array
    {
        return $this->fetchAll(
            'SELECT c.*, COUNT(tc.tutor_id) AS tutor_count
               FROM courses c
               LEFT JOIN tutor_courses tc ON tc.course_id = c.course_id
              GROUP BY c.course_id, c.course_code, c.course_name, c.status
              ORDER BY c.status, c.course_code'
        );
    }

    public function find(int $courseId): ?array
    {
        return $this->fetchOne('SELECT * FROM courses WHERE course_id = ?', [$courseId]);
    }

    // Is this code already used by another course?
    // When editing, pass the course's own id so it doesn't count as a duplicate of itself.
    public function codeExists(string $code, int $exceptCourseId = 0): bool
    {
        return (bool) $this->fetchValue(
            'SELECT COUNT(*) FROM courses WHERE course_code = ? AND course_id <> ?',
            [$code, $exceptCourseId]
        );
    }

    // New courses start as "active" (the column's default in the .sql file)
    public function create(string $code, string $name): void
    {
        $this->execute('INSERT INTO courses (course_code, course_name) VALUES (?, ?)', [$code, $name]);
    }

    public function update(int $courseId, string $code, string $name): void
    {
        $this->execute(
            'UPDATE courses SET course_code = ?, course_name = ? WHERE course_id = ?',
            [$code, $name, $courseId]
        );
    }

    // Archive or restore. Returns false for any status other than 'active'/'archived',
    // or if no course has that id.
    public function setStatus(int $courseId, string $status): bool
    {
        if (!in_array($status, ['active', 'archived'], true)) {
            return false;
        }
        return $this->execute('UPDATE courses SET status = ? WHERE course_id = ?', [$status, $courseId]) > 0;
    }
}
