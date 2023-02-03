<?php

namespace App\Entity;

use App\Repository\SubjectRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SubjectRepository::class)]
class Subject
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $teacherName = null;

    #[ORM\Column]
    private ?\DateTime $start_at = null;

    #[ORM\Column]
    private ?\DateTime $end_at = null;

    #[ORM\Column(length: 255)]
    private ?string $subjectName = null;

    #[ORM\Column(type: "datetime_immutable", options: ["default" => "CURRENT_TIMESTAMP"])]
    private $created_at;

    #[ORM\ManyToOne(inversedBy: 'subject_id')]
    private ?Schedule $schedule = null;

    #[ORM\Column(length: 7)]
    private ?string $background_color = null;

    #[ORM\Column(nullable: true)]
    private ?bool $all_day = null;

    #[ORM\Column(length: 7, nullable: true)]
    private ?string $border_color = null;

    #[ORM\Column(length: 7)]
    private ?string $text_color = null;

    #[ORM\ManyToOne(inversedBy: 'subjects')]
    private ?Schedule $schedule_id = null;


    public function __construct()
    {
        $this->created_at = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTeacherName(): ?string
    {
        return $this->teacherName;
    }

    public function setTeacherName(string $teacherName): self
    {
        $this->teacherName = $teacherName;

        return $this;
    }

    public function getStartAt(): ?\DateTime
    {
        return $this->start_at;
    }

    public function setStartAt(\DateTime $start_at): self
    {
        $this->start_at = $start_at;

        return $this;
    }

    public function getEndAt(): ?\DateTime
    {
        return $this->end_at;
    }

    public function setEndAt(\DateTime $end_at): self
    {
        $this->end_at = $end_at;

        return $this;
    }

    public function getSubjectName(): ?string
    {
        return $this->subjectName;
    }

    public function setSubjectName(string $subjectName): self
    {
        $this->subjectName = $subjectName;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getSchedule(): ?Schedule
    {
        return $this->schedule;
    }

    public function setSchedule(?Schedule $schedule): self
    {
        $this->schedule = $schedule;

        return $this;
    }

    public function getBackgroundColor(): ?string
    {
        return $this->background_color;
    }

    public function setBackgroundColor(string $background_color): self
    {
        $this->background_color = $background_color;

        return $this;
    }

    public function isAllDay(): ?bool
    {
        return $this->all_day;
    }

    public function setAllDay(?bool $all_day): self
    {
        $this->all_day = $all_day;

        return $this;
    }

    public function getBorderColor(): ?string
    {
        return $this->border_color;
    }

    public function setBorderColor(?string $border_color): self
    {
        $this->border_color = $border_color;

        return $this;
    }

    public function getTextColor(): ?string
    {
        return $this->text_color;
    }

    public function setTextColor(string $text_color): self
    {
        $this->text_color = $text_color;

        return $this;
    }

    public function getScheduleId(): ?Schedule
    {
        return $this->schedule_id;
    }

    public function setScheduleId(?Schedule $schedule_id): self
    {
        $this->schedule_id = $schedule_id;

        return $this;
    }
}
