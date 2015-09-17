<?php
// src/OC/PlatformBundle/Entity/Advert.php

namespace OC\PlatformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use OC\PlatformBundle\Validator\Antiflood;

/**
 * @ORM\Table(name="oc_advert")
 * @ORM\Entity(repositoryClass="OC\PlatformBundle\Entity\AdvertRepository")
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity(fields="title", message="Une annonce existe déjà avec ce titre.")
 */
class Advert
{
    /**
    * @Gedmo\Slug(fields={"title"})
    * @ORM\Column(length=128, unique=true)
    */
    private $slug;

    /**
     * @ORM\OneToMany(targetEntity="OC\PlatformBundle\Entity\Application", mappedBy="advert")
     */
    private $applications; // Notez le « s », une annonce est liée à plusieurs candidatures

    /**
    * @ORM\OneToOne(targetEntity="OC\PlatformBundle\Entity\Image", cascade={"persist", "remove"})
    * @Assert\Valid()
    */
    private $image;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     * @Assert\DateTime()
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     * @Assert\Length(min=10)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="author", type="string", length=255, unique=true)
     * @Assert\Length(min=2, minMessage="Le nom de l'auteur  doit faire au moins {{ limit }} caractères.")
     */
    private $author;

    /**
     * @Assert\NotBlank()
     * @Antiflood()
     */
    private $content;

    /**
    * @ORM\Column(name="updated_at", type="datetime", nullable=true)
    */
    private $updatedAt;

   /**
    *
    * @ORM\Column(name="published", type="boolean")
    */
    private $published = true;

    /**
    * @ORM\ManyToMany(targetEntity="OC\PlatformBundle\Entity\Category", cascade={"persist"})
    */
    private $categories;

   /**
    * @ORM\Column(name="nb_applications", type="integer")
    */
    private $nbApplications = 0;

    public function increaseApplication()
    {
     $this->nbApplications++;
    }

    public function decreaseApplication()
    {
     $this->nbApplications--;
    }
    /**
    * @Assert\Callback
    */
    public function isContentValid(ExecutionContextInterface $context)
    {
     $forbiddenWords = array('échec', 'abandon');

     if(preg_match('#'.implode('|', $forbiddenWords).'#', $this->getContent())){
      $context
       ->buildViolation('Contenu invalide car il contient un mot interdit.')
       ->atPath('content')
       ->addViolation()
      ;
     }
    }
   /**
    * Set updatedAt
    *
    * @param \DateTime $updatedAt
    * @return Advert
    */
   public function setUpdatedAt($updatedAt)
   {
       $this->updatedAt = $updatedAt;

       return $this;
   }

   /**
    * Get updatedAt
    *
    * @return \DateTime
    */
   public function getUpdatedAt()
   {
       return $this->updatedAt;
   }


   /**
   * @ORM\PostUpdate
   */
   public function updateDate()
   {
    $this->setUpdatedAt(new \Datetime());
   }

     // Comme la propriété $categories doit être un ArrayCollection,
     public function addApplication(Application $application)
     {
       $this->applications[] = $application;

       // On lie l'annonce à la candidature
       $application->setAdvert($this);

       return $this;
     }


     public function removeApplication(Application $application)
     {
       $this->applications->removeElement($application);
     }

     public function getApplications()
     {
       return $this->applications;
     }


  // Notez le singulier, on ajoute une seule catégorie à la fois
     public function addCategory(Category $category)
     {
       // Ici, on utilise l'ArrayCollection vraiment comme un tableau
       $this->categories[] = $category;
       return $this;
     }

     public function removeCategory(Category $category)
     {
       // Ici on utilise une méthode de l'ArrayCollection, pour supprimer la catégorie en argument
       $this->categories->removeElement($category);
     }

     // Notez le pluriel, on récupère une liste de catégories ici !
     public function getCategories()
     {
       return $this->categories;
     }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return Advert
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }


    /**
     * Set title
     *
     * @param string $title
     * @return Advert
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set author
     *
     * @param string $author
     * @return Advert
     */
    public function setAuthor($author)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author
     *
     * @return string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set content
     *
     * @param string $content
     * @return Advert
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set published
     *
     * @param boolean $published
     * @return Advert
     */
    public function setPublished($published)
    {
        $this->published = $published;

        return $this;
    }

    /**
     * Get published
     *
     * @return boolean
     */
    public function getPublished()
    {
        return $this->published;
    }

    public function __construct()
    {
      // Par défaut, la date de l'annonce est la date d'aujourd'hui
      $this->date = new \Datetime();

    }

    /**
     * Set image
     *
     * @param \OC\PlatformBundle\Entity\Image $image
     * @return Advert
     */
    public function setImage(\OC\PlatformBundle\Entity\Image $image = null)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return \OC\PlatformBundle\Entity\Image
     */
    public function getImage()
    {
        return $this->image;
    }



    /**
     * Set slug
     *
     * @param string $slug
     * @return Advert
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set nbApplications
     *
     * @param integer $nbApplications
     * @return Advert
     */
    public function setNbApplications($nbApplications)
    {
        $this->nbApplications = $nbApplications;

        return $this;
    }

    /**
     * Get nbApplications
     *
     * @return integer
     */
    public function getNbApplications()
    {
        return $this->nbApplications;
    }
}
