<?php

class Course extends Model
{
    public function active(): array
    {
        return $this->fetchAll('SELECT * FROM courses WHERE status = "active" ORDER BY course_code');
    }

    // all courses with how many tutors teach each one
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

    public function codeExists(string $code, int $exceptCourseId = 0): bool
    {
        return (bool) $this->fetchValue(
            'SELECT COUNT(*) FROM courses WHERE course_code = ? AND course_id <> ?',
            [$code, $exceptCourseId]
        );
    }

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

    public function setStatus(int $courseId, string $status): bool
    {
        if (!in_array($status, ['active', 'archived'], true)) {
            return false;
        }
        return $this->execute('UPDATE courses SET status = ? WHERE course_id = ?', [$status, $courseId]) > 0;
    }
}
