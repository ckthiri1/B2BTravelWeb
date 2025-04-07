<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250324145749 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cities CHANGE id id INT NOT NULL');
        $this->addSql('ALTER TABLE evennement DROP FOREIGN KEY fk_organisateur');
        $this->addSql('ALTER TABLE evennement ADD nom_e VARCHAR(255) NOT NULL, ADD des_e VARCHAR(255) NOT NULL, DROP NomE, DROP DesE, CHANGE IDE ide INT NOT NULL, CHANGE IDOr IDOr INT DEFAULT NULL, CHANGE event_type event_type VARCHAR(255) NOT NULL, CHANGE DateE date_e DATETIME NOT NULL');
        $this->addSql('DROP INDEX fk_organisateur ON evennement');
        $this->addSql('CREATE INDEX IDX_5C15C7747921AE6D ON evennement (IDOr)');
        $this->addSql('ALTER TABLE evennement ADD CONSTRAINT fk_organisateur FOREIGN KEY (IDOr) REFERENCES organisateur (IDOr) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE fidelite DROP INDEX idUser, ADD INDEX IDX_EF425B23FE6E88D7 (idUser)');
        $this->addSql('ALTER TABLE fidelite MODIFY IdF INT NOT NULL');
        $this->addSql('DROP INDEX `primary` ON fidelite');
        $this->addSql('ALTER TABLE fidelite DROP FOREIGN KEY fk_fidelite_rank');
        $this->addSql('ALTER TABLE fidelite ADD id_f INT NOT NULL, DROP IdF, CHANGE remise remise DOUBLE PRECISION NOT NULL, CHANGE IdRank IdRank INT DEFAULT NULL');
        $this->addSql('ALTER TABLE fidelite ADD PRIMARY KEY (id_f)');
        $this->addSql('DROP INDEX fk_fidelite_rank ON fidelite');
        $this->addSql('CREATE INDEX IDX_EF425B23FC28B34D ON fidelite (IdRank)');
        $this->addSql('ALTER TABLE fidelite ADD CONSTRAINT fk_fidelite_rank FOREIGN KEY (IdRank) REFERENCES rank (IDRang) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE hebergement CHANGE id_hebergement id_hebergement INT NOT NULL, CHANGE type type VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE organisateur CHANGE IDOr idor INT NOT NULL, CHANGE NomOr nom_or VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE rank CHANGE IDRang idrang INT NOT NULL, CHANGE NomRank nom_rank VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE reclamation DROP FOREIGN KEY fk_reclamation');
        $this->addSql('ALTER TABLE reclamation ADD date_r DATE NOT NULL, DROP DateR, CHANGE IDR idr INT NOT NULL, CHANGE Titre titre VARCHAR(255) NOT NULL, CHANGE Description description VARCHAR(255) NOT NULL, CHANGE Status status VARCHAR(255) NOT NULL');
        $this->addSql('DROP INDEX fk_reclamation ON reclamation');
        $this->addSql('CREATE INDEX IDX_CE6064046B3CA4B ON reclamation (id_user)');
        $this->addSql('ALTER TABLE reclamation ADD CONSTRAINT fk_reclamation FOREIGN KEY (id_user) REFERENCES user (user_id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reponse DROP FOREIGN KEY fk_rec');
        $this->addSql('ALTER TABLE reponse ADD description_rep VARCHAR(255) NOT NULL, ADD date_rep DATE NOT NULL, DROP DescriptionRep, DROP DateRep, CHANGE IDRep idrep INT NOT NULL, CHANGE IDR IDR INT DEFAULT NULL');
        $this->addSql('DROP INDEX fk_reponse ON reponse');
        $this->addSql('CREATE INDEX IDX_5FB6DEC7917AD584 ON reponse (IDR)');
        $this->addSql('ALTER TABLE reponse ADD CONSTRAINT fk_rec FOREIGN KEY (IDR) REFERENCES reclamation (IDR) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reservation_hebergement MODIFY id_resH INT NOT NULL');
        $this->addSql('DROP INDEX `primary` ON reservation_hebergement');
        $this->addSql('ALTER TABLE reservation_hebergement ADD id_res_h INT NOT NULL, DROP id_resH, CHANGE status status VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE reservation_hebergement ADD CONSTRAINT FK_843E00C0C1D14EBC FOREIGN KEY (idH) REFERENCES hebergement (id_hebergement) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reservation_hebergement ADD CONSTRAINT FK_843E00C05B893EAA FOREIGN KEY (idResV) REFERENCES reservation_voyage (id_resV) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reservation_hebergement ADD PRIMARY KEY (id_res_h)');
        $this->addSql('DROP INDEX fk_hebergement_reservation ON reservation_hebergement');
        $this->addSql('CREATE INDEX IDX_843E00C0C1D14EBC ON reservation_hebergement (idH)');
        $this->addSql('DROP INDEX fk_reservation_hebergement_resv ON reservation_hebergement');
        $this->addSql('CREATE INDEX IDX_843E00C05B893EAA ON reservation_hebergement (idResV)');
        $this->addSql('ALTER TABLE reservation_voyage MODIFY id_resV INT NOT NULL');
        $this->addSql('DROP INDEX `primary` ON reservation_voyage');
        $this->addSql('ALTER TABLE reservation_voyage DROP FOREIGN KEY fk_reservation_vol');
        $this->addSql('ALTER TABLE reservation_voyage DROP FOREIGN KEY fk_user');
        $this->addSql('ALTER TABLE reservation_voyage ADD id_res_v INT NOT NULL, ADD prix_total DOUBLE PRECISION NOT NULL, DROP id_resV, DROP prixTotal, CHANGE id_user id_user INT DEFAULT NULL');
        $this->addSql('ALTER TABLE reservation_voyage ADD PRIMARY KEY (id_res_v)');
        $this->addSql('DROP INDEX fk_user ON reservation_voyage');
        $this->addSql('CREATE INDEX IDX_776CC0CE6B3CA4B ON reservation_voyage (id_user)');
        $this->addSql('DROP INDEX fk_reservation_vol ON reservation_voyage');
        $this->addSql('CREATE INDEX IDX_776CC0CE97F87FB1 ON reservation_voyage (id_vol)');
        $this->addSql('ALTER TABLE reservation_voyage ADD CONSTRAINT fk_reservation_vol FOREIGN KEY (id_vol) REFERENCES vol (volID) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reservation_voyage ADD CONSTRAINT fk_user FOREIGN KEY (id_user) REFERENCES user (user_id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user ADD nbr_voyage INT NOT NULL, DROP nbrVoyage, CHANGE user_id user_id INT NOT NULL, CHANGE role role VARCHAR(255) NOT NULL, CHANGE salt salt VARCHAR(16) NOT NULL, CHANGE reset_token reset_token VARCHAR(255) NOT NULL, CHANGE token_expiry token_expiry DATETIME NOT NULL, CHANGE face_embedding face_embedding LONGTEXT NOT NULL, CHANGE face_image face_image VARCHAR(65535) NOT NULL, CHANGE voice_features voice_features LONGTEXT NOT NULL, CHANGE remember_token remember_token VARCHAR(255) NOT NULL, CHANGE remember_expiry remember_expiry DATETIME NOT NULL');
        $this->addSql('ALTER TABLE vol MODIFY volID INT NOT NULL');
        $this->addSql('DROP INDEX `primary` ON vol');
        $this->addSql('ALTER TABLE vol ADD vol_id INT NOT NULL, ADD date_depart DATE NOT NULL, ADD date_arrival DATE NOT NULL, ADD air_line VARCHAR(255) NOT NULL, ADD flight_number INT NOT NULL, ADD duree_vol VARCHAR(255) NOT NULL, ADD prix_vol INT NOT NULL, ADD type_vol VARCHAR(255) NOT NULL, DROP volID, DROP dateDepart, DROP dateArrival, DROP airLine, DROP flightNumber, DROP dureeVol, DROP prixVol, DROP typeVol, CHANGE status status VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE vol ADD CONSTRAINT FK_95C97EB5C8C21CF FOREIGN KEY (idVoyage) REFERENCES voyage (VID) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE vol ADD CONSTRAINT FK_95C97EBA76ED395 FOREIGN KEY (user_id) REFERENCES user (user_id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE vol ADD PRIMARY KEY (vol_id)');
        $this->addSql('DROP INDEX fk_vols_voyage ON vol');
        $this->addSql('CREATE INDEX IDX_95C97EB5C8C21CF ON vol (idVoyage)');
        $this->addSql('DROP INDEX fk_vol_user ON vol');
        $this->addSql('CREATE INDEX IDX_95C97EBA76ED395 ON vol (user_id)');
        $this->addSql('ALTER TABLE voyage CHANGE VID vid INT NOT NULL, CHANGE Description description LONGTEXT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cities CHANGE id id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE evennement DROP FOREIGN KEY FK_5C15C7747921AE6D');
        $this->addSql('ALTER TABLE evennement ADD NomE VARCHAR(255) NOT NULL, ADD DesE VARCHAR(255) NOT NULL, DROP nom_e, DROP des_e, CHANGE ide IDE INT AUTO_INCREMENT NOT NULL, CHANGE event_type event_type ENUM(\'CONFERENCE\', \'WEBINAR\', \'TRADE_SHOW\', \'WORKSHOP\', \'DEFAULT\') DEFAULT NULL, CHANGE IDOr IDOr INT NOT NULL, CHANGE date_e DateE DATETIME NOT NULL');
        $this->addSql('DROP INDEX idx_5c15c7747921ae6d ON evennement');
        $this->addSql('CREATE INDEX fk_organisateur ON evennement (IDOr)');
        $this->addSql('ALTER TABLE evennement ADD CONSTRAINT FK_5C15C7747921AE6D FOREIGN KEY (IDOr) REFERENCES organisateur (IDOr) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE fidelite DROP INDEX IDX_EF425B23FE6E88D7, ADD UNIQUE INDEX idUser (idUser)');
        $this->addSql('DROP INDEX `PRIMARY` ON fidelite');
        $this->addSql('ALTER TABLE fidelite DROP FOREIGN KEY FK_EF425B23FC28B34D');
        $this->addSql('ALTER TABLE fidelite ADD IdF INT AUTO_INCREMENT NOT NULL, DROP id_f, CHANGE remise remise DOUBLE PRECISION DEFAULT NULL, CHANGE IdRank IdRank INT NOT NULL');
        $this->addSql('ALTER TABLE fidelite ADD PRIMARY KEY (IdF)');
        $this->addSql('DROP INDEX idx_ef425b23fc28b34d ON fidelite');
        $this->addSql('CREATE INDEX fk_fidelite_rank ON fidelite (IdRank)');
        $this->addSql('ALTER TABLE fidelite ADD CONSTRAINT FK_EF425B23FC28B34D FOREIGN KEY (IdRank) REFERENCES rank (IDRang) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE hebergement CHANGE id_hebergement id_hebergement INT AUTO_INCREMENT NOT NULL, CHANGE type type ENUM(\'Hotel\', \'Hostel\', \'Maison\') NOT NULL');
        $this->addSql('ALTER TABLE organisateur CHANGE idor IDOr INT AUTO_INCREMENT NOT NULL, CHANGE nom_or NomOr VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE rank CHANGE idrang IDRang INT AUTO_INCREMENT NOT NULL, CHANGE nom_rank NomRank VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE reclamation DROP FOREIGN KEY FK_CE6064046B3CA4B');
        $this->addSql('ALTER TABLE reclamation ADD DateR DATE NOT NULL COMMENT \'none_4\', DROP date_r, CHANGE idr IDR INT AUTO_INCREMENT NOT NULL, CHANGE titre Titre VARCHAR(255) NOT NULL COMMENT \'none_4\', CHANGE description Description VARCHAR(255) NOT NULL COMMENT \'none_4\', CHANGE status Status VARCHAR(255) NOT NULL COMMENT \'none_4\'');
        $this->addSql('DROP INDEX idx_ce6064046b3ca4b ON reclamation');
        $this->addSql('CREATE INDEX fk_reclamation ON reclamation (id_user)');
        $this->addSql('ALTER TABLE reclamation ADD CONSTRAINT FK_CE6064046B3CA4B FOREIGN KEY (id_user) REFERENCES user (user_id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reponse DROP FOREIGN KEY FK_5FB6DEC7917AD584');
        $this->addSql('ALTER TABLE reponse ADD DescriptionRep VARCHAR(255) NOT NULL COMMENT \'IDR\', ADD DateRep DATE NOT NULL COMMENT \'IDR\', DROP description_rep, DROP date_rep, CHANGE idrep IDRep INT AUTO_INCREMENT NOT NULL, CHANGE IDR IDR INT NOT NULL COMMENT \'IDR\'');
        $this->addSql('DROP INDEX idx_5fb6dec7917ad584 ON reponse');
        $this->addSql('CREATE INDEX fk_reponse ON reponse (IDR)');
        $this->addSql('ALTER TABLE reponse ADD CONSTRAINT FK_5FB6DEC7917AD584 FOREIGN KEY (IDR) REFERENCES reclamation (IDR) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reservation_hebergement DROP FOREIGN KEY FK_843E00C0C1D14EBC');
        $this->addSql('ALTER TABLE reservation_hebergement DROP FOREIGN KEY FK_843E00C05B893EAA');
        $this->addSql('DROP INDEX `PRIMARY` ON reservation_hebergement');
        $this->addSql('ALTER TABLE reservation_hebergement DROP FOREIGN KEY FK_843E00C0C1D14EBC');
        $this->addSql('ALTER TABLE reservation_hebergement DROP FOREIGN KEY FK_843E00C05B893EAA');
        $this->addSql('ALTER TABLE reservation_hebergement ADD id_resH INT AUTO_INCREMENT NOT NULL, DROP id_res_h, CHANGE status status ENUM(\'EnAttente\', \'Resolue\') NOT NULL');
        $this->addSql('ALTER TABLE reservation_hebergement ADD PRIMARY KEY (id_resH)');
        $this->addSql('DROP INDEX idx_843e00c05b893eaa ON reservation_hebergement');
        $this->addSql('CREATE INDEX fk_reservation_hebergement_resV ON reservation_hebergement (idResV)');
        $this->addSql('DROP INDEX idx_843e00c0c1d14ebc ON reservation_hebergement');
        $this->addSql('CREATE INDEX fk_hebergement_reservation ON reservation_hebergement (idH)');
        $this->addSql('ALTER TABLE reservation_hebergement ADD CONSTRAINT FK_843E00C0C1D14EBC FOREIGN KEY (idH) REFERENCES hebergement (id_hebergement) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reservation_hebergement ADD CONSTRAINT FK_843E00C05B893EAA FOREIGN KEY (idResV) REFERENCES reservation_voyage (id_resV) ON DELETE CASCADE');
        $this->addSql('DROP INDEX `PRIMARY` ON reservation_voyage');
        $this->addSql('ALTER TABLE reservation_voyage DROP FOREIGN KEY FK_776CC0CE6B3CA4B');
        $this->addSql('ALTER TABLE reservation_voyage DROP FOREIGN KEY FK_776CC0CE97F87FB1');
        $this->addSql('ALTER TABLE reservation_voyage ADD id_resV INT AUTO_INCREMENT NOT NULL, ADD prixTotal NUMERIC(10, 2) NOT NULL, DROP id_res_v, DROP prix_total, CHANGE id_user id_user INT NOT NULL');
        $this->addSql('ALTER TABLE reservation_voyage ADD PRIMARY KEY (id_resV)');
        $this->addSql('DROP INDEX idx_776cc0ce97f87fb1 ON reservation_voyage');
        $this->addSql('CREATE INDEX fk_reservation_vol ON reservation_voyage (id_vol)');
        $this->addSql('DROP INDEX idx_776cc0ce6b3ca4b ON reservation_voyage');
        $this->addSql('CREATE INDEX fk_user ON reservation_voyage (id_user)');
        $this->addSql('ALTER TABLE reservation_voyage ADD CONSTRAINT FK_776CC0CE6B3CA4B FOREIGN KEY (id_user) REFERENCES user (user_id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reservation_voyage ADD CONSTRAINT FK_776CC0CE97F87FB1 FOREIGN KEY (id_vol) REFERENCES vol (volID) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user ADD nbrVoyage INT DEFAULT NULL, DROP nbr_voyage, CHANGE user_id user_id INT AUTO_INCREMENT NOT NULL, CHANGE role role ENUM(\'user\', \'admin\') NOT NULL, CHANGE salt salt BINARY(16) NOT NULL, CHANGE reset_token reset_token VARCHAR(255) DEFAULT NULL, CHANGE token_expiry token_expiry DATETIME DEFAULT NULL, CHANGE face_embedding face_embedding TEXT DEFAULT NULL, CHANGE face_image face_image BLOB DEFAULT NULL, CHANGE voice_features voice_features TEXT DEFAULT NULL, CHANGE remember_token remember_token VARCHAR(255) DEFAULT NULL, CHANGE remember_expiry remember_expiry DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE vol DROP FOREIGN KEY FK_95C97EB5C8C21CF');
        $this->addSql('ALTER TABLE vol DROP FOREIGN KEY FK_95C97EBA76ED395');
        $this->addSql('DROP INDEX `PRIMARY` ON vol');
        $this->addSql('ALTER TABLE vol DROP FOREIGN KEY FK_95C97EB5C8C21CF');
        $this->addSql('ALTER TABLE vol DROP FOREIGN KEY FK_95C97EBA76ED395');
        $this->addSql('ALTER TABLE vol ADD volID INT AUTO_INCREMENT NOT NULL, ADD dateDepart DATE NOT NULL, ADD dateArrival DATE NOT NULL, ADD airLine VARCHAR(255) NOT NULL, ADD flightNumber INT NOT NULL, ADD dureeVol VARCHAR(255) NOT NULL, ADD prixVol INT NOT NULL, ADD typeVol ENUM(\'Aller\', \'Aller-Retour\') NOT NULL, DROP vol_id, DROP date_depart, DROP date_arrival, DROP air_line, DROP flight_number, DROP duree_vol, DROP prix_vol, DROP type_vol, CHANGE status status ENUM(\'NON_RESERVER\', \'RESERVER\') DEFAULT \'NON_RESERVER\'');
        $this->addSql('ALTER TABLE vol ADD PRIMARY KEY (volID)');
        $this->addSql('DROP INDEX idx_95c97eb5c8c21cf ON vol');
        $this->addSql('CREATE INDEX fk_vols_voyage ON vol (idVoyage)');
        $this->addSql('DROP INDEX idx_95c97eba76ed395 ON vol');
        $this->addSql('CREATE INDEX fk_vol_user ON vol (user_id)');
        $this->addSql('ALTER TABLE vol ADD CONSTRAINT FK_95C97EB5C8C21CF FOREIGN KEY (idVoyage) REFERENCES voyage (VID) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE vol ADD CONSTRAINT FK_95C97EBA76ED395 FOREIGN KEY (user_id) REFERENCES user (user_id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE voyage CHANGE vid VID INT AUTO_INCREMENT NOT NULL, CHANGE description Description TEXT DEFAULT NULL');
    }
}
